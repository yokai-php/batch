<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Launcher;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Throwable;
use Yokai\Batch\BatchStatus;
use Yokai\Batch\Event\PostExecuteEvent;
use Yokai\Batch\Event\PreExecuteEvent;
use Yokai\Batch\Factory\JobExecutionFactory;
use Yokai\Batch\Factory\UniqidJobExecutionIdGenerator;
use Yokai\Batch\Job\JobInterface;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Launcher\SimpleJobLauncher;
use Yokai\Batch\Registry\JobRegistry;
use Yokai\Batch\Test\Storage\InMemoryJobExecutionStorage;

class SimpleJobLauncherTest extends TestCase
{
    use ProphecyTrait;

    private const JOB_NAME = 'phpunit';
    private const VALID_JOB_ID = '123abc';
    private const NOT_EXECUTABLE_JOB_ID = '456def';

    /**
     * @var ObjectProphecy&JobInterface
     */
    private ObjectProphecy $job;

    /**
     * @var ObjectProphecy&EventDispatcherInterface
     */
    private ObjectProphecy $dispatcher;

    private SimpleJobLauncher $launcher;

    protected function setUp(): void
    {
        $this->job = $this->prophesize(JobInterface::class);
        $this->dispatcher = $this->prophesize(EventDispatcherInterface::class);

        $container = $this->prophesize(ContainerInterface::class);
        $container->has(self::JOB_NAME)->willReturn(true);
        $container->get(self::JOB_NAME)->willReturn($this->job->reveal());

        $jobExecutionStorage = new InMemoryJobExecutionStorage(
            JobExecution::createRoot(self::VALID_JOB_ID, self::JOB_NAME),
            JobExecution::createRoot(
                self::NOT_EXECUTABLE_JOB_ID,
                self::JOB_NAME,
                new BatchStatus(BatchStatus::COMPLETED)
            )
        );

        $this->launcher = new SimpleJobLauncher(
            new JobRegistry($container->reveal()),
            new JobExecutionFactory(new UniqidJobExecutionIdGenerator()),
            $jobExecutionStorage,
            $this->dispatcher->reveal()
        );
    }

    /**
     * @dataProvider launch
     */
    public function testLaunch(array $config): void
    {
        $jobExecutionAssertions = Argument::allOf(
            Argument::type(JobExecution::class),
            Argument::which('getJobName', self::JOB_NAME)
        );
        $this->job->execute($jobExecutionAssertions)
            ->shouldBeCalledTimes(1)
            ->will(function (array $args): void {
                /** @var JobExecution $execution */
                $execution = $args[0];
                $execution->setStartTime(new \DateTime());
                $execution->getSummary()->set('foo', 'FOO');
                $execution->setEndTime(new \DateTime());
            });

        $jobExecution = $this->launcher->launch(self::JOB_NAME, $config);

        $this->dispatcher->dispatch(Argument::type(PreExecuteEvent::class))
            ->shouldHaveBeenCalledTimes(1);
        $this->dispatcher->dispatch(Argument::type(PostExecuteEvent::class))
            ->shouldHaveBeenCalledTimes(1);

        self::assertNotNull($jobExecution->getStartTime());
        self::assertNotNull($jobExecution->getEndTime());
        self::assertSame('FOO', $jobExecution->getSummary()->get('foo'));
    }

    /**
     * @dataProvider errors
     */
    public function testLaunchJobCatchErrors(Throwable $error): void
    {
        $this->job->execute(Argument::any())
            ->willThrow($error);

        $execution = $this->launcher->launch(self::JOB_NAME);

        $this->dispatcher->dispatch(Argument::type(PreExecuteEvent::class))
            ->shouldHaveBeenCalledTimes(1);
        $this->dispatcher->dispatch(Argument::type(PostExecuteEvent::class))
            ->shouldHaveBeenCalledTimes(1);

        self::assertSame(self::JOB_NAME, $execution->getJobName());
        self::assertTrue($execution->getStatus()->is(BatchStatus::FAILED));
        self::assertSame(\get_class($error), $execution->getFailures()[0]->getClass());
        self::assertSame($error->getMessage(), $execution->getFailures()[0]->getMessage());
    }

    public function testLaunchJobNotExecutable(): void
    {
        $this->job->execute(Argument::any())
            ->shouldNotBeCalled();

        $execution = $this->launcher->launch(self::JOB_NAME, ['_id' => self::NOT_EXECUTABLE_JOB_ID]);

        $this->dispatcher->dispatch(Argument::type(PreExecuteEvent::class))
            ->shouldNotHaveBeenCalled();
        $this->dispatcher->dispatch(Argument::type(PostExecuteEvent::class))
            ->shouldNotHaveBeenCalled();

        self::assertStringContainsString(
            'WARNING: Job execution not allowed to be executed',
            (string)$execution->getLogs()
        );
    }

    public function launch(): \Generator
    {
        yield 'Launch with no id' => [[]];
        yield 'Launch with valid id' => [['_id' => self::VALID_JOB_ID]];
        yield 'Launch with unknown id' => [['_id' => 'unknown id']];
    }

    public function errors(): \Generator
    {
        yield [new \Exception('Triggered for test purpose')];
        yield [new \DivisionByZeroError('Triggered for test purpose')];
    }
}
