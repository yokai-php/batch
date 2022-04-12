<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Job;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\EventDispatcher\EventDispatcherInterface;
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
use Yokai\Batch\Warning;

class JobExecutorTest extends TestCase
{
    use ProphecyTrait;

    private const JOB_NAME = 'phpunit';

    /**
     * @var ObjectProphecy&JobInterface
     */
    private ObjectProphecy $job;

    /**
     * @var ObjectProphecy&EventDispatcherInterface
     */
    private ObjectProphecy $dispatcher;

    private JobExecutor $executor;

    protected function setUp(): void
    {
        $this->job = $this->prophesize(JobInterface::class);
        $this->dispatcher = $this->prophesize(EventDispatcherInterface::class);

        $this->executor = new JobExecutor(
            JobRegistry::fromJobArray([self::JOB_NAME => $this->job->reveal()]),
            new InMemoryJobExecutionStorage(),
            $this->dispatcher->reveal()
        );
    }

    public function testLaunch(): void
    {
        $execution = JobExecution::createRoot('123', self::JOB_NAME);
        $this->job->execute($execution)
            ->shouldBeCalledTimes(1)
            ->will(function (array $args): void {
                /** @var JobExecution $execution */
                $execution = $args[0];
                $execution->getSummary()->set('foo', 'FOO');
                $execution->addWarning(new Warning('Test warning on purpose'));
            });

        $this->executor->execute($execution);

        $this->dispatcher->dispatch(Argument::type(PreExecuteEvent::class))
            ->shouldHaveBeenCalledTimes(1);
        $this->dispatcher->dispatch(Argument::type(PostExecuteEvent::class))
            ->shouldHaveBeenCalledTimes(1);

        self::assertNotNull($execution->getStartTime());
        self::assertNotNull($execution->getEndTime());
        self::assertSame(BatchStatus::COMPLETED, $execution->getStatus()->getValue());
        self::assertSame('FOO', $execution->getSummary()->get('foo'));
        $logs = (string)$execution->getLogs();
        self::assertStringContainsString('DEBUG: Starting job', $logs);
        self::assertStringContainsString('INFO: Job executed successfully', $logs);
        self::assertStringContainsString('DEBUG: Job produced summary', $logs);
    }

    /**
     * @dataProvider errors
     */
    public function testLaunchJobCatchErrors(Throwable $error): void
    {
        $execution = JobExecution::createRoot('123', self::JOB_NAME);
        $this->job->execute($execution)
            ->willThrow($error);

        $this->executor->execute($execution);

        $this->dispatcher->dispatch(Argument::type(PreExecuteEvent::class))
            ->shouldHaveBeenCalledTimes(1);
        $this->dispatcher->dispatch(Argument::type(ExceptionEvent::class))
            ->shouldHaveBeenCalledTimes(1);
        $this->dispatcher->dispatch(Argument::type(PostExecuteEvent::class))
            ->shouldHaveBeenCalledTimes(1);

        self::assertNotNull($execution->getStartTime());
        self::assertNotNull($execution->getEndTime());
        self::assertSame(BatchStatus::FAILED, $execution->getStatus()->getValue());
        self::assertSame(\get_class($error), $execution->getFailures()[0]->getClass());
        self::assertSame($error->getMessage(), $execution->getFailures()[0]->getMessage());
        $logs = (string)$execution->getLogs();
        self::assertStringContainsString('DEBUG: Starting job', $logs);
        self::assertStringContainsString('ERROR: Job did not executed successfully', $logs);
    }

    public function testLaunchErrorWithStatusListener(): void
    {
        $execution = JobExecution::createRoot('123', self::JOB_NAME);
        $this->job->execute($execution)
            ->willThrow($exception = new \RuntimeException());

        $this->dispatcher->dispatch(Argument::type(ExceptionEvent::class))
            ->shouldBeCalledTimes(1)
            ->will(function (array $args) use ($exception) {
                /** @var ExceptionEvent $event */
                $event = $args[0];
                Assert::assertSame($exception, $event->getException());
                $event->setStatus(BatchStatus::COMPLETED);
            });

        $this->executor->execute($execution);

        $this->dispatcher->dispatch(Argument::type(PreExecuteEvent::class))
            ->shouldHaveBeenCalledTimes(1);
        $this->dispatcher->dispatch(Argument::type(PostExecuteEvent::class))
            ->shouldHaveBeenCalledTimes(1);

        self::assertNotNull($execution->getStartTime());
        self::assertNotNull($execution->getEndTime());
        self::assertSame(BatchStatus::COMPLETED, $execution->getStatus()->getValue());
        $logs = (string)$execution->getLogs();
        self::assertStringContainsString('DEBUG: Starting job', $logs);
        self::assertStringContainsString('INFO: Job executed successfully', $logs);
    }

    public function testLaunchJobNotExecutable(): void
    {
        $this->job->execute(Argument::any())
            ->shouldNotBeCalled();

        $execution = JobExecution::createRoot('123', self::JOB_NAME, new BatchStatus(BatchStatus::COMPLETED));
        $this->executor->execute($execution);

        $this->dispatcher->dispatch(Argument::type(PreExecuteEvent::class))
            ->shouldNotHaveBeenCalled();
        $this->dispatcher->dispatch(Argument::type(PostExecuteEvent::class))
            ->shouldNotHaveBeenCalled();

        $logs = (string)$execution->getLogs();
        self::assertStringContainsString('WARNING: Job execution not allowed to be executed', $logs);
        self::assertStringNotContainsString('DEBUG: Starting job', $logs);
    }

    public function errors(): \Generator
    {
        yield [new \Exception('Triggered for test purpose')];
        yield [new \DivisionByZeroError('Triggered for test purpose')];
    }
}
