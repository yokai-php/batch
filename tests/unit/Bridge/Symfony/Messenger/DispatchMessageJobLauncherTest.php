<?php

namespace Yokai\Batch\Tests\Unit\Bridge\Symfony\Messenger;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Yokai\Batch\BatchStatus;
use Yokai\Batch\Bridge\Symfony\Messenger\DispatchMessageJobLauncher;
use Yokai\Batch\Bridge\Symfony\Messenger\LaunchJobMessage;
use Yokai\Batch\Factory\JobExecutionFactory;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Storage\JobExecutionStorageInterface;

final class DispatchMessageJobLauncherTest extends TestCase
{
    public function testLaunch(): void
    {
        $storage = $this->prophesize(JobExecutionStorageInterface::class);
        $jobExecutionAssertions = Argument::that(
            function ($jobExecution): bool {
                return $jobExecution instanceof JobExecution
                    && $jobExecution->getJobName() === 'testing'
                    && $jobExecution->getId() === '123456789'
                    && $jobExecution->getStatus()->is(BatchStatus::PENDING)
                    && $jobExecution->getParameters()->get('foo') === ['bar'];
            }
        );
        $storage->store($jobExecutionAssertions)
            ->shouldBeCalled();
        $storage->retrieve('testing', '123456789')
            ->shouldBeCalled()
            ->willReturn($jobExecution = JobExecution::createRoot('123456789-refreshed', 'testing'));

        $messageBus = $this->prophesize(MessageBusInterface::class);
        $messageAssertions = Argument::that(
            function ($message): bool {
                return $message instanceof LaunchJobMessage
                    && $message->getJobName() === 'testing'
                    && $message->getConfiguration() === ['_id' => '123456789', 'foo' => ['bar']];
            }
        );
        $messageBus->dispatch($messageAssertions)
            ->shouldBeCalled()
            ->willReturn(new Envelope(new LaunchJobMessage('unused')));

        $jobLauncher = new DispatchMessageJobLauncher(
            new JobExecutionFactory(),
            $storage->reveal(),
            $messageBus->reveal()
        );

        self::assertSame(
            $jobExecution,
            $jobLauncher->launch('testing', ['_id' => '123456789', 'foo' => ['bar']])
        );
    }
}
