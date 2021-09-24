<?php

declare(strict_types=1);

namespace Yokai\Batch\Test\Storage;

use Yokai\Batch\Exception\CannotRemoveJobExecutionException;
use Yokai\Batch\Exception\JobExecutionNotFoundException;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Storage\JobExecutionStorageInterface;

final class InMemoryJobExecutionStorage implements JobExecutionStorageInterface
{
    /**
     * @var JobExecution[]
     */
    private array $executions = [];

    public function __construct(JobExecution ...$executions)
    {
        foreach ($executions as $execution) {
            $this->executions[$execution->getJobName() . '/' . $execution->getId()] = $execution;
        }
    }

    /**
     * @inheritDoc
     */
    public function store(JobExecution $execution): void
    {
        $this->executions[self::buildKeyFrom($execution)] = $execution;
    }

    /**
     * @inheritDoc
     */
    public function remove(JobExecution $execution): void
    {
        $key = self::buildKeyFrom($execution);
        if (!isset($this->executions[$key])) {
            throw new CannotRemoveJobExecutionException($execution->getJobName(), $execution->getId());
        }

        unset($this->executions[$key]);
    }

    /**
     * @inheritDoc
     */
    public function retrieve(string $jobName, string $executionId): JobExecution
    {
        $key = self::buildKey($jobName, $executionId);
        if (!isset($this->executions[$key])) {
            throw new JobExecutionNotFoundException($jobName, $executionId);
        }

        return $this->executions[$key];
    }

    /**
     * @return JobExecution[]
     */
    public function getExecutions(): array
    {
        return \array_values($this->executions);
    }

    private static function buildKeyFrom(JobExecution $execution): string
    {
        return self::buildKey($execution->getJobName(), $execution->getId());
    }

    private static function buildKey(string $jobName, string $executionId): string
    {
        return $jobName . '/' . $executionId;
    }
}
