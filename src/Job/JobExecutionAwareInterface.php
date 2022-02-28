<?php

declare(strict_types=1);

namespace Yokai\Batch\Job;

use Yokai\Batch\JobExecution;

/**
 * A class implementing this interface will gain access
 * to current {@see JobExecution}.
 */
interface JobExecutionAwareInterface
{
    public function setJobExecution(JobExecution $jobExecution): void;
}
