<?php

declare(strict_types=1);

namespace Yokai\Batch\Job;

use Yokai\Batch\JobExecution;
use Yokai\Batch\Launcher\SimpleJobLauncher;

/**
 * A job is the place where everything starts.
 */
interface JobInterface
{
    /**
     * Execute the job.
     * Called by {@see SimpleJobLauncher}.
     *
     * @param JobExecution $jobExecution Current execution representing
     *                                   the one that is going to start
     */
    public function execute(JobExecution $jobExecution): void;
}
