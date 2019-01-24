<?php declare(strict_types=1);

namespace Yokai\Batch\Job;

use Yokai\Batch\JobExecution;

interface JobExecutionAwareInterface
{
    /**
     * @param JobExecution $jobExecution
     */
    public function setJobExecution(JobExecution $jobExecution): void;
}
