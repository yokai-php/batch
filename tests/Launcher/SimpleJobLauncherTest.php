<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Launcher;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\EventDispatcher\EventDispatcherInterface;
use Yokai\Batch\BatchStatus;
use Yokai\Batch\Factory\JobExecutionFactory;
use Yokai\Batch\Job\JobExecutionAccessor;
use Yokai\Batch\Job\JobExecutor;
use Yokai\Batch\Job\JobInterface;
use Yokai\Batch\Launcher\SimpleJobLauncher;
use Yokai\Batch\Registry\JobRegistry;
use Yokai\Batch\Test\Factory\SequenceJobExecutionIdGenerator;
use Yokai\Batch\Test\Storage\InMemoryJobExecutionStorage;

class SimpleJobLauncherTest extends TestCase
{
    use ProphecyTrait;

    public function test(): void
    {
        $job = $this->prophesize(JobInterface::class);
        $dispatcher = $this->prophesize(EventDispatcherInterface::class);

        $launcher = new SimpleJobLauncher(
            new JobExecutionAccessor(
                new JobExecutionFactory(new SequenceJobExecutionIdGenerator(['123'])),
                $jobExecutionStorage = new InMemoryJobExecutionStorage(),
            ),
            new JobExecutor(
                JobRegistry::fromJobArray(['phpunit' => $job->reveal()]),
                $jobExecutionStorage,
                $dispatcher->reveal()
            )
        );

        $execution = $launcher->launch('phpunit');
        self::assertSame('phpunit', $execution->getJobName());
        self::assertSame('123', $execution->getId());
        self::assertSame(BatchStatus::COMPLETED, $execution->getStatus()->getValue());
        self::assertSame($execution, $jobExecutionStorage->retrieve('phpunit', '123'));
    }
}
