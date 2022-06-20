<?php

declare(strict_types=1);

namespace Yokai\Batch\Job;

use Yokai\Batch\Exception\JobExecutionNotFoundException;
use Yokai\Batch\Factory\JobExecutionFactory;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Storage\JobExecutionStorageInterface;

/**
 * This class is responsible for {@see JobExecution} retrieval.
 * It will either find it from {@see JobExecutionStorageInterface}.
 * Or create and store new one using {@see JobExecutionFactory}.
 */
final class JobExecutionAccessor
{
    public function __construct(
        private JobExecutionFactory $jobExecutionFactory,
        private JobExecutionStorageInterface $jobExecutionStorage,
    ) {
    }

    /**
     * Retrieve or create a {@see JobExecution}.
     *
     * @param array<string, mixed> $configuration
     */
    public function get(string $name, array $configuration): JobExecution
    {
        $id = $configuration['_id'] ?? null;
        if (is_string($id)) {
            try {
                return $this->jobExecutionStorage->retrieve($name, $id);
            } catch (JobExecutionNotFoundException) {
            }
        }

        $jobExecution = $this->jobExecutionFactory->create($name, $configuration);
        $this->jobExecutionStorage->store($jobExecution);

        return $jobExecution;
    }
}
