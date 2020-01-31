<?php

namespace Yokai\Batch\Tests\Unit\Bridge\Symfony\Console;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Yokai\Batch\Bridge\Symfony\Console\CommandRunner;
use Yokai\Batch\Bridge\Symfony\Console\RunCommandJobLauncher;
use Yokai\Batch\Factory\JobExecutionFactory;
use Yokai\Batch\JobExecution;
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
        $storage->store(Argument::type(JobExecution::class))
            ->shouldBeCalledTimes(1);

        $launcher = new RunCommandJobLauncher(new JobExecutionFactory(), $commandRunner->reveal(), $storage->reveal(), 'test.log');
        $launcher->launch('testing', $config);
    }
}
