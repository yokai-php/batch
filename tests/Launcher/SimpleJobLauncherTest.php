<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Launcher;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Yokai\Batch\Factory\JobExecutionFactory;
use Yokai\Batch\Job\JobInterface;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Launcher\SimpleJobLauncher;
use Yokai\Batch\Registry\JobRegistry;
use Yokai\Batch\Storage\JobExecutionStorageInterface;

class SimpleJobLauncherTest extends TestCase
{
    use ProphecyTrait;

    public function testLaunch(): void
    {
        $jobExecutionAssertions = Argument::allOf(
            Argument::type(JobExecution::class),
            Argument::which('getJobName', 'export')
        );
        /** @var ObjectProphecy|JobInterface $job */
        $job = $this->prophesize(JobInterface::class);
        $job->execute($jobExecutionAssertions)
            ->shouldBeCalledTimes(1)
            ->will(function (array $args) {
                /** @var JobExecution $execution */
                $execution = $args[0];
                $execution->setStartTime(new \DateTime());
                $execution->getSummary()->set('foo', 'FOO');
                $execution->setEndTime(new \DateTime());
            });

        /** @var ContainerInterface|ObjectProphecy $container */
        $container = $this->prophesize(ContainerInterface::class);
        $container->has('export')->willReturn(true);
        $container->get('export')->willReturn($job->reveal());

        $jobRegistry = new JobRegistry($container->reveal());
        $jobExecutionFactory = new JobExecutionFactory();
        $jobExecutionStorage = $this->prophesize(JobExecutionStorageInterface::class);

        $launcher = new SimpleJobLauncher($jobRegistry, $jobExecutionFactory, $jobExecutionStorage->reveal(), null);
        $jobExecution = $launcher->launch('export');

        self::assertNotNull($jobExecution->getStartTime());
        self::assertNotNull($jobExecution->getEndTime());
        self::assertSame('FOO', $jobExecution->getSummary()->get('foo'));
    }

    public function testLaunchJobCatchException(): void
    {
        $jobExecutionAssertions = Argument::allOf(
            Argument::type(JobExecution::class),
            Argument::which('getJobName', 'export')
        );
        /** @var ObjectProphecy|JobInterface $job */
        $job = $this->prophesize(JobInterface::class);
        $job->execute($jobExecutionAssertions)
            ->shouldBeCalledTimes(1)
            ->willThrow(new \Exception());

        /** @var ContainerInterface|ObjectProphecy $container */
        $container = $this->prophesize(ContainerInterface::class);
        $container->has('export')->willReturn(true);
        $container->get('export')->willReturn($job->reveal());

        $jobRegistry = new JobRegistry($container->reveal());
        $jobExecutionFactory = new JobExecutionFactory();
        $jobExecutionStorage = $this->prophesize(JobExecutionStorageInterface::class);

        $launcher = new SimpleJobLauncher($jobRegistry, $jobExecutionFactory, $jobExecutionStorage->reveal(), null);
        $launcher->launch('export');
    }

    public function testLaunchJobCatchFatal(): void
    {
        $jobExecutionAssertions = Argument::allOf(
            Argument::type(JobExecution::class),
            Argument::which('getJobName', 'export')
        );
        /** @var ObjectProphecy|JobInterface $job */
        $job = $this->prophesize(JobInterface::class);
        $job->execute($jobExecutionAssertions)
            ->shouldBeCalledTimes(1)
            ->will(function () {
                $var = 10 / 0;
            });

        /** @var ContainerInterface|ObjectProphecy $container */
        $container = $this->prophesize(ContainerInterface::class);
        $container->has('export')->willReturn(true);
        $container->get('export')->willReturn($job->reveal());

        $jobRegistry = new JobRegistry($container->reveal());
        $jobExecutionFactory = new JobExecutionFactory();
        $jobExecutionStorage = $this->prophesize(JobExecutionStorageInterface::class);

        $launcher = new SimpleJobLauncher($jobRegistry, $jobExecutionFactory, $jobExecutionStorage->reveal(), null);
        $launcher->launch('export');
    }
}
