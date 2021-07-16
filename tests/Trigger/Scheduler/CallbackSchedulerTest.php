<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Trigger\Scheduler;

use Yokai\Batch\JobExecution;
use Yokai\Batch\Trigger\Scheduler\CallbackScheduler;
use PHPUnit\Framework\TestCase;
use Yokai\Batch\Trigger\Scheduler\ScheduledJob;

class CallbackSchedulerTest extends TestCase
{
    public function test(): void
    {
        $scheduler = new CallbackScheduler([
            [fn() => true, 'always_triggered', ['config' => 'value'], 'always_triggered_job_id'],
            [fn() => false, 'never_triggered'],
            [fn() => true, 'always_triggered_with_defaults'],
        ]);

        /** @var ScheduledJob[] $scheduled */
        $scheduled = \iterator_to_array($scheduler->get(JobExecution::createRoot('123', 'testing')));
        self::assertCount(2, $scheduled);
        self::assertSame('always_triggered', $scheduled[0]->getJobName());
        self::assertSame(['config' => 'value'], $scheduled[0]->getParameters());
        self::assertSame('always_triggered_job_id', $scheduled[0]->getId());
        self::assertSame('always_triggered_with_defaults', $scheduled[1]->getJobName());
        self::assertSame([], $scheduled[1]->getParameters());
        self::assertNull($scheduled[1]->getId());
    }
}
