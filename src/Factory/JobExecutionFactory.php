<?php

declare(strict_types=1);

namespace Yokai\Batch\Factory;

use Yokai\Batch\JobExecution;
use Yokai\Batch\JobParameters;

final class JobExecutionFactory
{
    /**
     * @param string $name
     * @param array  $configuration
     *
     * @return JobExecution
     */
    public function create(string $name, array $configuration = []): JobExecution
    {
        $configuration['_id'] = $configuration['_id'] ?? uniqid();

        return JobExecution::createRoot($configuration['_id'], $name, null, new JobParameters($configuration));
    }
}
