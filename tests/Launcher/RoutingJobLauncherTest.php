<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Launcher;

use PHPUnit\Framework\TestCase;
use Psr\Container\NotFoundExceptionInterface;
use Yokai\Batch\Exception\UnexpectedValueException;
use Yokai\Batch\Launcher\RoutingJobLauncher;
use Yokai\Batch\Test\ArrayContainer;
use Yokai\Batch\Test\Factory\SequenceJobExecutionIdGenerator;
use Yokai\Batch\Test\Launcher\BufferingJobLauncher;

class RoutingJobLauncherTest extends TestCase
{
    public function test(): void
    {
        $launcher1 = new BufferingJobLauncher(new SequenceJobExecutionIdGenerator(['123']));
        $launcher2 = new BufferingJobLauncher(new SequenceJobExecutionIdGenerator(['abc']));
        $defaultLauncher = new BufferingJobLauncher(new SequenceJobExecutionIdGenerator(['def1']));

        $launcher = new RoutingJobLauncher(
            new ArrayContainer([
                'launcher1' => $launcher1,
                'launcher2' => $launcher2,
            ]),
            $defaultLauncher,
            [
                'job1' => 'launcher1',
                'job2' => 'launcher2',
            ],
        );

        $executionJob1 = $launcher->launch('job1');
        self::assertSame('job1', $executionJob1->getJobName());
        self::assertSame('123', $executionJob1->getId());
        $executionJob2 = $launcher->launch('job2');
        self::assertSame('job2', $executionJob2->getJobName());
        self::assertSame('abc', $executionJob2->getId());
        $executionJob2 = $launcher->launch('job3');
        self::assertSame('job3', $executionJob2->getJobName());
        self::assertSame('def1', $executionJob2->getId());
        self::assertCount(1, $launcher1->getExecutions());
        self::assertCount(1, $launcher2->getExecutions());
        self::assertCount(1, $defaultLauncher->getExecutions());
    }

    public function testConfiguredWithUnknownJobLauncher(): void
    {
        $launcher = new RoutingJobLauncher(
            new ArrayContainer([
                'launcher1' => new BufferingJobLauncher(new SequenceJobExecutionIdGenerator(['123'])),
            ]),
            new BufferingJobLauncher(new SequenceJobExecutionIdGenerator(['def1'])),
            [
                'job1' => 'unknown_launcher',
            ],
        );

        self::expectException(NotFoundExceptionInterface::class);
        $launcher->launch('job1');
    }

    public function testConfiguredWithNoJobLauncher(): void
    {
        $launcher = new RoutingJobLauncher(
            new ArrayContainer([
                'launcher1' => new \DateTime(),
            ]),
            new BufferingJobLauncher(new SequenceJobExecutionIdGenerator(['def1'])),
            [
                'job1' => 'launcher1',
            ],
        );

        self::expectException(UnexpectedValueException::class);
        $launcher->launch('job1');
    }
}
