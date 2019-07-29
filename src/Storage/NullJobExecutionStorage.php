<?php declare(strict_types=1);

namespace Yokai\Batch\Storage;

use Yokai\Batch\Exception\JobExecutionNotFoundException;
use Yokai\Batch\JobExecution;

final class NullJobExecutionStorage implements JobExecutionStorageInterface
{
    /**
     * @inheritDoc
     */
    public function store(JobExecution $execution): void
    {
    }

    /**
     * @inheritDoc
     */
    public function retrieve(string $jobName, string $executionId): JobExecution
    {
        try {
            throw new \LogicException(__CLASS__.' is not able to retrieve any job execution.');
        } catch (\Throwable $exception) {
            throw new JobExecutionNotFoundException($jobName, $executionId, $exception);
        }
    }
}
