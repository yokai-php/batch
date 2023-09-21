<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Job;

use PHPUnit\Framework\TestCase;
use Yokai\Batch\BatchStatus;
use Yokai\Batch\Job\JobExecutor;
use Yokai\Batch\Job\JobInterface;
use Yokai\Batch\Job\JobWithChildJobs;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Registry\JobRegistry;
use Yokai\Batch\Test\Storage\InMemoryJobExecutionStorage;

class JobWithChildJobsTest extends TestCase
{
    private InMemoryJobExecutionStorage $storage;

    protected function setUp(): void
    {
        $this->storage = new InMemoryJobExecutionStorage();
    }

    public function test(): void
    {
        $execution = $this->execute([
            'import' => new class() implements JobInterface {
                public function execute(JobExecution $jobExecution): void
                {
                    $jobExecution->getSummary()->set('executed', true);
                }
            },
            'report' => new class() implements JobInterface {
                public function execute(JobExecution $jobExecution): void
                {
                    $jobExecution->getSummary()->set('executed', true);
                }
            },
        ]);

        self::assertStatusSame(BatchStatus::COMPLETED, $execution);
        self::assertCount(2, $execution->getChildExecutions());
        self::assertNotNull($import = $execution->getChildExecution('import'));
        self::assertStatusSame(BatchStatus::COMPLETED, $import);
        self::assertTrue($import->getSummary()->get('executed'));
        self::assertLogsContains('DEBUG: Starting child job {"job":"import"}', $execution);
        self::assertLogsContains('INFO: Child job executed successfully {"job":"import"}', $execution);
        self::assertNotNull($report = $execution->getChildExecution('report'));
        self::assertStatusSame(BatchStatus::COMPLETED, $report);
        self::assertTrue($report->getSummary()->get('executed'));
        self::assertLogsContains('DEBUG: Starting child job {"job":"report"}', $execution);
        self::assertLogsContains('INFO: Child job executed successfully {"job":"report"}', $execution);
    }

    public function testNoChildren(): void
    {
        $execution = $this->execute([
        ]);

        self::assertStatusSame(BatchStatus::COMPLETED, $execution);
        self::assertCount(0, $execution->getChildExecutions());
        self::assertLogsNotContains('Child job executed successfully', $execution);
        self::assertLogsNotContains('Child job did not executed successfully', $execution);
    }

    public function testFirstChildFailing(): void
    {
        $execution = $this->execute([
            'import' => new class() implements JobInterface {
                public function execute(JobExecution $jobExecution): void
                {
                    throw new \RuntimeException('Expected failure');
                }
            },
            'report' => new class() implements JobInterface {
                public function execute(JobExecution $jobExecution): void
                {
                    throw new \LogicException('Should never be executed');
                }
            },
        ]);

        self::assertStatusSame(BatchStatus::FAILED, $execution);
        self::assertCount(2, $execution->getChildExecutions());
        self::assertNotNull($import = $execution->getChildExecution('import'));
        self::assertStatusSame(BatchStatus::FAILED, $import);
        self::assertLogsContains('ERROR: Child job did not executed successfully {"job":"import"', $execution);
        self::assertNotNull($report = $execution->getChildExecution('report'));
        self::assertStatusSame(BatchStatus::ABANDONED, $report);
        self::assertLogsContains('WARNING: Child job will not be executed {"job":"report"}', $execution);
    }

    /**
     * @param array<string, JobInterface> $children
     */
    private function execute(array $children): JobExecution
    {
        (new JobExecutor(
            JobRegistry::fromJobArray(['test' => JobWithChildJobs::withAnonymousChildren($children, $this->storage)]),
            $this->storage,
            null,
        ))->execute($execution = JobExecution::createRoot('650c0f7907774', 'test'));

        return $execution;
    }

    private static function assertStatusSame(int $expected, JobExecution $execution): void
    {
        self::assertSame($expected, $execution->getStatus()->getValue());
    }

    private static function assertLogsContains(string $expected, JobExecution $execution): void
    {
        self::assertStringContainsString($expected, (string)$execution->getLogs());
    }

    private static function assertLogsNotContains(string $expected, JobExecution $execution): void
    {
        self::assertStringNotContainsString($expected, (string)$execution->getLogs());
    }
}
