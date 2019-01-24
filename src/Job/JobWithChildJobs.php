<?php declare(strict_types=1);

namespace Yokai\Batch\Job;

use Yokai\Batch\BatchStatus;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Registry\JobRegistry;

final class JobWithChildJobs extends AbstractJob
{
    /**
     * @var JobRegistry
     */
    private $jobRegistry;

    /**
     * @var iterable|string[]
     */
    private $childJobs;

    /**
     * @param JobRegistry       $jobRegistry
     * @param iterable|string[] $childJobs
     */
    public function __construct(JobRegistry $jobRegistry, iterable $childJobs)
    {
        $this->jobRegistry = $jobRegistry;
        $this->childJobs = $childJobs;
    }

    /**
     * @inheritDoc
     */
    protected function doExecute(JobExecution $jobExecution): void
    {
        foreach ($this->childJobs as $jobName) {
            $jobExecution->addChildExecution(
                $childExecution = $jobExecution->createChildExecution($jobName)
            );

            // If the job was marked as unsuccessful, the child will not be executed, and marked as abandoned
            if ($jobExecution->getStatus()->isUnsuccessful()) {
                $childExecution->setStatus(BatchStatus::ABANDONED);
                continue;
            }

            $this->jobRegistry->get($jobName)->execute($childExecution);

            // Check if the child executed successfully, replicate the status to the job otherwise
            if ($childExecution->getStatus()->isUnsuccessful()) {
                $jobExecution->setStatus($childExecution->getStatus()->getValue());
            }
        }
    }
}
