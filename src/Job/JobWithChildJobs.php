<?php

declare(strict_types=1);

namespace Yokai\Batch\Job;

use Yokai\Batch\BatchStatus;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Storage\JobExecutionStorageInterface;

class JobWithChildJobs implements JobInterface
{
    public function __construct(
        private JobExecutionStorageInterface $executionStorage,
        private JobExecutor $jobExecutor,
        /**
         * @var iterable<string>
         */
        private iterable $childJobs,
    ) {
    }

    /**
     * @inheritDoc
     */
    final public function execute(JobExecution $jobExecution): void
    {
        $logger = $jobExecution->getLogger();
        foreach ($this->childJobs as $jobName) {
            $jobExecution->addChildExecution($childExecution = $jobExecution->createChildExecution($jobName));

            // If the job was marked as unsuccessful, the child will not be executed, and marked as abandoned
            if ($jobExecution->getStatus()->isUnsuccessful()) {
                $childExecution->setStatus(BatchStatus::ABANDONED);
                $logger->warning('Child job will not be executed', ['job' => $jobName]);

                continue;
            }

            $logger->debug('Starting child job', ['job' => $jobName]);
            $this->jobExecutor->execute($childExecution);

            // Check if the child executed successfully, replicate the status to the job otherwise
            if ($childExecution->getStatus()->isUnsuccessful()) {
                $jobExecution->setStatus($childExecution->getStatus()->getValue());
                $logger->error('Child job did not executed successfully', ['job' => $jobName]);
            } else {
                $logger->info('Child job executed successfully', ['job' => $jobName]);
            }

            $this->executionStorage->store($jobExecution->getRootExecution());
        }

        $this->executionStorage->store($jobExecution->getRootExecution());
    }
}
