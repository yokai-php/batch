<?php

namespace Yokai\Batch\Tests\Integration;

use Yokai\Batch\BatchStatus;
use Yokai\Batch\Job\AbstractJob;
use Yokai\Batch\Job\JobInterface;
use Yokai\Batch\Job\JobWithChildJobs;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Storage\JobExecutionStorageInterface;

class JobWithFailingDummyChidlrenTest extends JobTestCase
{
    protected function createJob(JobExecutionStorageInterface $executionStorage): JobInterface
    {
        return new JobWithChildJobs(
            $executionStorage,
            self::createJobRegistry(
                [
                    'prepare' => new class extends AbstractJob
                    {
                        protected function doExecute(JobExecution $jobExecution): void
                        {
                            throw new \Exception('Critical dummy exception');
                        }
                    },
                    'do' => new class extends AbstractJob
                    {
                        protected function doExecute(JobExecution $jobExecution): void
                        {
                            // this job should not be executed
                            $jobExecution->getSummary()->set('done', true);
                        }
                    },
                ]
            ),
            ['prepare', 'do']
        );
    }

    protected function getJobName(): string
    {
        return 'job-with-failing-dummy-children';
    }

    protected function assertAgainstExecution(
        JobExecutionStorageInterface $jobExecutionStorage,
        JobExecution $jobExecution
    ): void {
        parent::assertAgainstExecution($jobExecutionStorage, $jobExecution);

        self::assertSame(BatchStatus::FAILED, $jobExecution->getStatus()->getValue());

        $prepareChildExecution = $jobExecution->getChildExecution('prepare');
        self::assertSame(BatchStatus::FAILED, $prepareChildExecution->getStatus()->getValue());

        $doChildExecution = $jobExecution->getChildExecution('do');
        self::assertSame(BatchStatus::ABANDONED, $doChildExecution->getStatus()->getValue());
        self::assertNull($doChildExecution->getSummary()->get('done'));
    }
}
