<?php

declare(strict_types=1);

namespace Yokai\Batch\Launcher;

use Yokai\Batch\Job\JobExecutionAccessor;
use Yokai\Batch\Job\JobExecutor;
use Yokai\Batch\JobExecution;

/**
 * This {@see JobLauncherInterface} executes all job directly in the same PHP process.
 * This is the simplest (and thus default) implementation.
 */
class SimpleJobLauncher implements JobLauncherInterface
{
    public function __construct(
        private JobExecutionAccessor $jobExecutionAccessor,
        private JobExecutor $jobExecutor,
    ) {
    }

    public function launch(string $name, array $configuration = []): JobExecution
    {
        $execution = $this->jobExecutionAccessor->get($name, $configuration);
        $this->jobExecutor->execute($execution);

        return $execution;
    }
}
