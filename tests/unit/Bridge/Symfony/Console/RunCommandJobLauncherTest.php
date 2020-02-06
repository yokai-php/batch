<?php

namespace Yokai\Batch\Tests\Unit\Bridge\Symfony\Console;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Yokai\Batch\BatchStatus;
use Yokai\Batch\Bridge\Symfony\Console\CommandRunner;
use Yokai\Batch\Bridge\Symfony\Console\RunCommandJobLauncher;
use Yokai\Batch\Factory\JobExecutionFactory;
use Yokai\Batch\JobExecution;
use Yokai\Batch\JobParameters;
use Yokai\Batch\Storage\JobExecutionStorageInterface;

class RunCommandJobLauncherTest extends TestCase
{
    public function testLaunch(): void
    {
        $config = ['_id' => '123456789', 'foo' => ['bar']];
        $arguments = ['job' => 'testing', 'configuration' => '{"_id":"123456789","foo":["bar"]}'];

        /** @var CommandRunner|ObjectProphecy $commandRunner */
        $commandRunner = $this->prophesize(CommandRunner::class);
        $commandRunner->runAsync('yokai:batch:run', 'test.log', $arguments)
            ->shouldBeCalledTimes(1);

        /** @var JobExecutionStorageInterface|ObjectProphecy $storage */
        $storage = $this->prophesize(JobExecutionStorageInterface::class);

        $storage->store(
            Argument::that(
                function ($jobExecution) use ($config) {
                    if (!$jobExecution instanceof JobExecution) {
                        return false;
                    }
                    if ($jobExecution->getJobName() !== 'testing') {
                        return false;
                    }
                    if ($jobExecution->getId() !== $config['_id']) {
                        return false;
                    }
                    if ($jobExecution->getStatus()
                            ->getValue() !== BatchStatus::PENDING) {
                        return false;
                    }
                    if ($jobExecution->getParameters()
                            ->get('foo') !== ['bar']) {
                        return false;
                    }

                    return true;
                }
            )
        )
            ->shouldBeCalledTimes(1);

        $launcher = new RunCommandJobLauncher(new JobExecutionFactory(), $commandRunner->reveal(), $storage->reveal(), 'test.log');
        $launcher->launch('testing', $config);
    }
}
