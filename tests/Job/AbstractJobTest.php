<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Job;

use PHPUnit\Framework\TestCase;
use Yokai\Batch\BatchStatus;
use Yokai\Batch\Job\AbstractJob;
use Yokai\Batch\JobExecution;

class AbstractJobTest extends TestCase
{
    public function testRegular(): void
    {
        $job = new class extends AbstractJob {
            public bool $executed = false;
            protected function doExecute(JobExecution $jobExecution): void
            {
                $this->executed = true;
            }
        };

        $execution = JobExecution::createRoot('123', 'testing');
        $job->execute($execution);

        self::assertTrue($job->executed);

        self::assertNotNull($execution->getStartTime());
        self::assertNotNull($execution->getEndTime());
        self::assertSame(BatchStatus::COMPLETED, $execution->getStatus()->getValue());
    }

    public function testNotExecutable(): void
    {
        $job = new class extends AbstractJob {
            public bool $executed = false;
            protected function doExecute(JobExecution $jobExecution): void
            {
                $this->executed = true;
            }
        };

        $execution = JobExecution::createRoot('123', 'testing', new BatchStatus(BatchStatus::FAILED));

        $job->execute($execution);

        self::assertFalse($job->executed);

        self::assertNull($execution->getStartTime());
        self::assertNull($execution->getEndTime());
        self::assertSame(BatchStatus::FAILED, $execution->getStatus()->getValue());
        self::assertStringContainsString(
            'ERROR: Job is not executable',
            (string)$execution->getLogs()
        );
    }

    public function testError(): void
    {
        $job = new class extends AbstractJob {
            protected function doExecute(JobExecution $jobExecution): void
            {
                throw new \RuntimeException('Error triggered by tests.');
            }
        };

        $execution = JobExecution::createRoot('123', 'testing');
        $job->execute($execution);

        self::assertNotNull($execution->getStartTime());
        self::assertNotNull($execution->getEndTime());
        self::assertSame(BatchStatus::FAILED, $execution->getStatus()->getValue());
        self::assertSame(\RuntimeException::class, $execution->getFailures()[0]->getClass());
        self::assertSame('Error triggered by tests.', $execution->getFailures()[0]->getMessage());
    }
}
