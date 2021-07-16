<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Trigger;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Yokai\Batch\Factory\UniqidJobExecutionIdGenerator;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Test\Factory\SequenceJobExecutionIdGenerator;
use Yokai\Batch\Test\Launcher\BufferingJobLauncher;
use Yokai\Batch\Trigger\Scheduler\CallbackScheduler;
use Yokai\Batch\Trigger\Scheduler\TimeScheduler;
use Yokai\Batch\Trigger\TriggerScheduledJobsJob;

class TriggerScheduledJobsJobTest extends TestCase
{
    public function test(): void
    {
        $job = new TriggerScheduledJobsJob(
            [
                new CallbackScheduler([
                    [fn() => false, 'never_triggered'],
                    [fn() => true, 'triggered_with_defaults'],
                ]),
                new TimeScheduler([
                    [new DateTimeImmutable('yesterday'), 'triggered', ['config' => 'value'], 'triggered_job_id'],
                    [new DateTimeImmutable('tomorrow'), 'not_triggered'],
                ]),
            ],
            $launcher = new BufferingJobLauncher(new SequenceJobExecutionIdGenerator(['123']))
        );

        $job->execute($jobExecution = JobExecution::createRoot('123456', 'testing'));

        self::assertCount(2, $launcher->getExecutions());
        self::assertSame('triggered_with_defaults', $launcher->getExecutions()[0]->getJobName());
        self::assertSame('123', $launcher->getExecutions()[0]->getId());
        self::assertSame(
            ['_id' => '123'],
            \iterator_to_array($launcher->getExecutions()[0]->getParameters()->getIterator())
        );
        self::assertSame('triggered', $launcher->getExecutions()[1]->getJobName());
        self::assertSame('triggered_job_id', $launcher->getExecutions()[1]->getId());
        self::assertSame(
            ['config' => 'value', '_id' => 'triggered_job_id'],
            \iterator_to_array($launcher->getExecutions()[1]->getParameters()->getIterator())
        );
        self::assertSame([
            [
                'scheduler' => CallbackScheduler::class,
                'job' => 'triggered_with_defaults',
                'id' => '123',
            ],
            [
                'scheduler' => TimeScheduler::class,
                'job' => 'triggered',
                'id' => 'triggered_job_id',
            ],
        ], $jobExecution->getSummary()->get('jobs'));
        $logs = (string)$jobExecution->getLogs();
        self::assertStringContainsString(
            'INFO: Launched scheduled job. ' .
            '{"scheduler":"' . \preg_quote(CallbackScheduler::class) . '","job":"triggered_with_defaults","id":"123"}',
            $logs
        );
        self::assertStringContainsString(
            'INFO: Launched scheduled job. ' .
            '{"scheduler":"' . \preg_quote(TimeScheduler::class) . '","job":"triggered","id":"triggered_job_id"}',
            $logs
        );
    }

    public function testWithNoScheduler(): void
    {
        $job = new TriggerScheduledJobsJob(
            [],
            $launcher = new BufferingJobLauncher(new UniqidJobExecutionIdGenerator())
        );

        $job->execute($jobExecution = JobExecution::createRoot('123456', 'testing'));
        self::assertSame([], $launcher->getExecutions());
        self::assertSame([], $jobExecution->getSummary()->get('jobs'));
        self::assertStringNotContainsString('Launched scheduled job', (string)$jobExecution->getLogs());
    }
}
