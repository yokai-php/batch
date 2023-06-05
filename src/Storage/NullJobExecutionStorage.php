<?php

declare(strict_types=1);

namespace Yokai\Batch\Storage;

use Yokai\Batch\Exception\JobExecutionNotFoundException;
use Yokai\Batch\JobExecution;

/**
 * This {@see JobExecutionStorageInterface} do not persist anything.
 */
final class NullJobExecutionStorage implements JobExecutionStorageInterface
{
    public function store(JobExecution $execution): void
    {
    }

    public function remove(JobExecution $execution): void
    {
    }

    public function retrieve(string $jobName, string $executionId): JobExecution
    {
        try {
            throw new \LogicException(self::class . ' is not able to retrieve any job execution.');
        } catch (\Throwable $exception) {
            throw new JobExecutionNotFoundException($jobName, $executionId, $exception);
        }
    }
}
