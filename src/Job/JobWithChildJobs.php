<?php

declare(strict_types=1);

namespace Yokai\Batch\Job;

use Psr\EventDispatcher\EventDispatcherInterface;
use Yokai\Batch\BatchStatus;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Registry\JobRegistry;
use Yokai\Batch\Storage\JobExecutionStorageInterface;

/**
 * This {@see JobInterface} will execute by triggering child jobs.
 * If a child job fails, following child jobs won't be executed.
 *
 * @final use {@see AbstractDecoratedJob} instead.
 */
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
     * When creating {@see JobWithChildJobs}, you might prefer that child jobs are not available from the outside.
     * You can do it by yourself, but this method will do it for you in one line.
     *
     * @param array<string, JobInterface> $children
     */
    public static function withAnonymousChildren(
        array $children,
        JobExecutionStorageInterface $executionStorage,
        EventDispatcherInterface $eventDispatcher = null,
    ): self {
        return new self(
            $executionStorage,
            new JobExecutor(JobRegistry::fromJobArray($children), $executionStorage, $eventDispatcher),
            \array_keys($children),
        );
    }

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
