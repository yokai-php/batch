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
     * @param string $name          Job's name to launch
     * @param array  $configuration Job's parameters
     *
     * @return JobExecution Information about job's execution
     *
     * @phpstan-param array<string, mixed> $configuration
     */
    public function launch(string $name, array $configuration = []): JobExecution;
}
