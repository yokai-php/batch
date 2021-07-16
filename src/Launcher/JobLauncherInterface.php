<?php

declare(strict_types=1);

namespace Yokai\Batch\Launcher;

use Yokai\Batch\JobExecution;

interface JobLauncherInterface
{
    /**
     * @param string $name
     * @param array  $configuration
     *
     * @return JobExecution
     *
     * @phpstan-param array<string, mixed> $configuration
     */
    public function launch(string $name, array $configuration = []): JobExecution;
}
