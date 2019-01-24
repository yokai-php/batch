<?php declare(strict_types=1);

namespace Yokai\Batch\Job;

use Yokai\Batch\JobExecution;

interface JobInterface
{
    /**
     * @param JobExecution $jobExecution
     */
    public function execute(JobExecution $jobExecution): void;
}
