<?php

namespace Yokai\Batch\Tests\Unit\Bridge\Symfony\Messenger;

use PHPUnit\Framework\TestCase;
use Yokai\Batch\Bridge\Symfony\Messenger\LaunchJobMessage;
use Yokai\Batch\Bridge\Symfony\Messenger\LaunchJobMessageHandler;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Launcher\JobLauncherInterface;

final class LaunchJobMessageHandlerTest extends TestCase
{
    public function testInvoke(): void
    {
        $jobLauncher = $this->prophesize(JobLauncherInterface::class);
        $jobLauncher->launch('foo', ['bar' => 'BAR'])
            ->shouldBeCalled()
            ->willReturn(JobExecution::createRoot('123456', 'foo'));

        $handler = new LaunchJobMessageHandler($jobLauncher->reveal());
        $handler->__invoke(new LaunchJobMessage('foo', ['bar' => 'BAR']));
    }
}
