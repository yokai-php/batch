<?php

declare(strict_types=1);

namespace Yokai\Batch\Launcher;

use Yokai\Batch\JobExecution;

/**
 * The job launcher is responsible for executing a job, or scheduling it's execution.
 */
interface JobLauncherInterface
{
    /**
     * Launch a job.
     *
     * @param string               $name          Job's name to launch
     * @param array<string, mixed> $configuration Job's parameters
     *
     * @return JobExecution Information about job's execution
     */
    public function launch(string $name, array $configuration = []): JobExecution;
}
