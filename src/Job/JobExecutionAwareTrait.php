<?php

declare(strict_types=1);

namespace Yokai\Batch\Job;

use Yokai\Batch\JobExecution;

/**
 * Covers {@see JobExecutionAwareInterface}.
 */
trait JobExecutionAwareTrait
{
    private JobExecution $jobExecution;

    public function setJobExecution(JobExecution $jobExecution): void
    {
        $this->jobExecution = $jobExecution;
    }

    /**
     * Get current job execution.
     */
    protected function getJobExecution(): JobExecution
    {
        return $this->jobExecution;
    }

    /**
     * Get root execution of current job execution.
     */
    protected function getRootExecution(): JobExecution
    {
        return $this->jobExecution->getRootExecution();
    }
}
