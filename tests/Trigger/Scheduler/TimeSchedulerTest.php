<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Trigger\Scheduler;

use DateTime;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Trigger\Scheduler\ScheduledJob;
use Yokai\Batch\Trigger\Scheduler\TimeScheduler;

class TimeSchedulerTest extends TestCase
{
    public function test(): void
    {
        $scheduler = new TimeScheduler([
            [new DateTimeImmutable('yesterday'), 'yesterday', ['config' => 'value'], 'yesterday_job_id'],
            [new DateTimeImmutable('tomorrow'), 'tomorrow'],
            [new DateTime('1 second ago'), 'just_now'],
        ]);

        $execution = JobExecution::createRoot('123', 'testing');
        $execution->setStartTime(new DateTimeImmutable());

        /** @var ScheduledJob[] $scheduled */
        $scheduled = \iterator_to_array($scheduler->get($execution));
        self::assertCount(2, $scheduled);
        self::assertSame('yesterday', $scheduled[0]->getJobName());
        self::assertSame(['config' => 'value'], $scheduled[0]->getParameters());
        self::assertSame('yesterday_job_id', $scheduled[0]->getId());
        self::assertSame('just_now', $scheduled[1]->getJobName());
        self::assertSame([], $scheduled[1]->getParameters());
        self::assertNull($scheduled[1]->getId());
    }
}
