<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Job;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Throwable;
use Yokai\Batch\BatchStatus;
use Yokai\Batch\Event\ExceptionEvent;
use Yokai\Batch\Event\PostExecuteEvent;
use Yokai\Batch\Event\PreExecuteEvent;
use Yokai\Batch\Job\JobExecutor;
use Yokai\Batch\Job\JobInterface;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Registry\JobRegistry;
use Yokai\Batch\Test\Storage\InMemoryJobExecutionStorage;
use Yokai\Batch\Tests\Dummy\DebugEventDispatcher;
use Yokai\Batch\Warning;

class JobExecutorTest extends TestCase
{
    private Stub&JobInterface $job;
    private DebugEventDispatcher $dispatcher;
    private JobExecutor $executor;

    protected function setUp(): void
    {
        $this->job = $this->createMock(JobInterface::class);
        $this->dispatcher = new DebugEventDispatcher();
        $this->executor = new JobExecutor(
            JobRegistry::fromJobArray(['test.job_executor' => $this->job]),
            new InMemoryJobExecutionStorage(),
            $this->dispatcher,
        );
    }

    public function testLaunch(): void
    {
        $execution = JobExecution::createRoot('123', 'test.job_executor');
        $this->job->expects($this->once())
            ->method('execute')
            ->with($execution)
            ->willReturnCallback(function (JobExecution $execution): void {
                $execution->getSummary()->set('foo', 'FOO');
                $execution->addWarning(new Warning('Test warning on purpose'));
            });

        $this->executor->execute($execution);

        self::assertNotNull($execution->getStartTime());
        self::assertNotNull($execution->getEndTime());
        self::assertSame(BatchStatus::COMPLETED, $execution->getStatus()->getValue());
        self::assertSame('FOO', $execution->getSummary()->get('foo'));
        $logs = (string)$execution->getLogs();
        self::assertStringContainsString('DEBUG: Starting job', $logs);
        self::assertStringContainsString('INFO: Job executed successfully', $logs);
        self::assertStringContainsString('DEBUG: Job produced summary', $logs);
        $events = $this->dispatcher->getEvents();
        self::assertCount(2, $events);
        self::assertInstanceOf(PreExecuteEvent::class, $events[0] ?? null);
        self::assertInstanceOf(PostExecuteEvent::class, $events[1] ?? null);
    }

    #[DataProvider('errors')]
    public function testLaunchJobCatchErrors(Throwable $error): void
    {
        $execution = JobExecution::createRoot('123', 'test.job_executor');
        $this->job->expects($this->once())
            ->method('execute')
            ->with($execution)
            ->willThrowException($error);

        $this->executor->execute($execution);

        self::assertNotNull($execution->getStartTime());
        self::assertNotNull($execution->getEndTime());
        self::assertSame(BatchStatus::FAILED, $execution->getStatus()->getValue());
        self::assertSame($error::class, $execution->getFailures()[0]->getClass());
        self::assertSame($error->getMessage(), $execution->getFailures()[0]->getMessage());
        $logs = (string)$execution->getLogs();
        self::assertStringContainsString('DEBUG: Starting job', $logs);
        self::assertStringContainsString('ERROR: Job did not executed successfully', $logs);
        $events = $this->dispatcher->getEvents();
        self::assertCount(3, $events);
        self::assertInstanceOf(PreExecuteEvent::class, $events[0] ?? null);
        self::assertInstanceOf(ExceptionEvent::class, $events[1] ?? null);
        self::assertInstanceOf(PostExecuteEvent::class, $events[2] ?? null);
    }

    public function testLaunchErrorWithStatusListener(): void
    {
        $execution = JobExecution::createRoot('123', 'test.job_executor');
        $this->job->expects($this->once())
            ->method('execute')
            ->with($execution)
            ->willThrowException($exception = new \RuntimeException());

        $this->dispatcher->addListener(
            ExceptionEvent::class,
            function (ExceptionEvent $event) use ($exception) {
                Assert::assertSame($exception, $event->getException());
                $event->setStatus(BatchStatus::COMPLETED);
            },
        );

        $this->executor->execute($execution);

        self::assertNotNull($execution->getStartTime());
        self::assertNotNull($execution->getEndTime());
        self::assertSame(BatchStatus::COMPLETED, $execution->getStatus()->getValue());
        $logs = (string)$execution->getLogs();
        self::assertStringContainsString('DEBUG: Starting job', $logs);
        self::assertStringContainsString('INFO: Job executed successfully', $logs);
        $events = $this->dispatcher->getEvents();
        self::assertCount(3, $events);
        self::assertInstanceOf(PreExecuteEvent::class, $events[0] ?? null);
        self::assertInstanceOf(ExceptionEvent::class, $events[1] ?? null);
        self::assertInstanceOf(PostExecuteEvent::class, $events[2] ?? null);
    }

    public function testLaunchJobNotExecutable(): void
    {
        $this->job->expects($this->never())
            ->method('execute');

        $execution = JobExecution::createRoot('123', 'test.job_executor', new BatchStatus(BatchStatus::COMPLETED));
        $this->executor->execute($execution);

        $logs = (string)$execution->getLogs();
        self::assertStringContainsString('WARNING: Job execution not allowed to be executed', $logs);
        self::assertStringNotContainsString('DEBUG: Starting job', $logs);
        $events = $this->dispatcher->getEvents();
        self::assertCount(0, $events);
    }

    public static function errors(): \Generator
    {
        yield [new \Exception('Triggered for test purpose')];
        yield [new \DivisionByZeroError('Triggered for test purpose')];
    }
}
