<?php

declare(strict_types=1);

namespace Yokai\Batch\Job;

use Yokai\Batch\JobExecution;

/**
 * A job is the place where everything starts.
 */
interface JobInterface
{
    /**
     * Execute the job.
     * Called by {@see JobExecutor}.
     *
     * @param JobExecution $jobExecution Current execution representing
     *                                   the one that is going to start
     */
    public function execute(JobExecution $jobExecution): void;
}
