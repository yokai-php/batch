<?php

declare(strict_types=1);

namespace Yokai\Batch\Job;

use Yokai\Batch\JobExecution;

/**
 * A class implementing this interface will gain access
 * to current {@see JobExecution}.
 *
 * Default implementation from {@see JobExecutionAwareTrait} can be used.
 */
interface JobExecutionAwareInterface
{
    /**
     * Set execution to the job component.
     */
    public function setJobExecution(JobExecution $jobExecution): void;
}
