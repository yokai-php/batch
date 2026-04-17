<?php

declare(strict_types=1);

namespace Yokai\Batch\Test\Storage;

use Yokai\Batch\Exception\CannotRemoveJobExecutionException;
use Yokai\Batch\Exception\JobExecutionNotFoundException;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Storage\Query;
use Yokai\Batch\Storage\QueryableJobExecutionStorageInterface;
use Yokai\Batch\Storage\SortDirection;

/**
 * This {@see QueryableJobExecutionStorageInterface} should be used in tests.
 * It stores executions in memory and allows fetching them for assertions.
 */
final class InMemoryJobExecutionStorage implements QueryableJobExecutionStorageInterface
{
    /**
     * @var array<string, JobExecution>
     */
    private array $executions = [];

    public function __construct(JobExecution ...$executions)
    {
        foreach ($executions as $execution) {
            $this->executions[self::buildKeyFrom($execution)] = $execution;
        }
    }

    public function store(JobExecution $execution): void
    {
        $this->executions[self::buildKeyFrom($execution)] = $execution;
    }

    public function remove(JobExecution $execution): void
    {
        $key = self::buildKeyFrom($execution);
        if (!isset($this->executions[$key])) {
            throw new CannotRemoveJobExecutionException($execution->getJobName(), $execution->getId());
        }

        unset($this->executions[$key]);
    }

    public function retrieve(string $jobName, string $executionId): JobExecution
    {
        $key = self::buildKey($jobName, $executionId);
        if (!isset($this->executions[$key])) {
            throw new JobExecutionNotFoundException($jobName, $executionId);
        }

        return $this->executions[$key];
    }

    public function list(string $jobName): iterable
    {
        foreach ($this->executions as $execution) {
            if ($execution->getJobName() === $jobName) {
                yield $execution;
            }
        }
    }

    public function query(Query $query): iterable
    {
        return $this->applyQuery($query);
    }

    public function count(Query $query): int
    {
        return \count($this->applyQuery($query, ignoreLimit: true));
    }

    public function purge(Query $query): void
    {
        foreach ($this->applyQuery($query, ignoreLimit: true) as $execution) {
            unset($this->executions[self::buildKeyFrom($execution)]);
        }
    }

    /**
     * @return JobExecution[]
     */
    public function getExecutions(): array
    {
        return \array_values($this->executions);
    }

    /**
     * @return JobExecution[]
     */
    private function applyQuery(Query $query, bool $ignoreLimit = false): array
    {
        $executions = \array_values($this->executions);

        // Filter
        $jobs = $query->jobs();
        $ids = $query->ids();
        $statuses = $query->statuses();
        $startDateFrom = $query->startTime()?->getFrom();
        $startDateTo = $query->startTime()?->getTo();
        $endDateFrom = $query->endTime()?->getFrom();
        $endDateTo = $query->endTime()?->getTo();

        $executions = \array_filter($executions, function (JobExecution $execution) use (
            $jobs,
            $ids,
            $statuses,
            $startDateFrom,
            $startDateTo,
            $endDateFrom,
            $endDateTo,
        ): bool {
            if ($jobs !== [] && !\in_array($execution->getJobName(), $jobs, true)) {
                return false;
            }
            if ($ids !== [] && !\in_array($execution->getId(), $ids, true)) {
                return false;
            }
            if ($statuses !== [] && !\in_array($execution->getStatus(), $statuses, true)) {
                return false;
            }
            $startTime = $execution->getStartTime();
            if ($startDateFrom !== null && ($startTime === null || $startTime < $startDateFrom)) {
                return false;
            }
            if ($startDateTo !== null && ($startTime === null || $startTime > $startDateTo)) {
                return false;
            }
            $endTime = $execution->getEndTime();
            if ($endDateFrom !== null && ($endTime === null || $endTime < $endDateFrom)) {
                return false;
            }
            if ($endDateTo !== null && ($endTime === null || $endTime > $endDateTo)) {
                return false;
            }

            return true;
        });

        // Sort
        $order = match ($query->sort()) {
            SortDirection::StartAsc  => static fn(JobExecution $a, JobExecution $b): int => $a->getStartTime() <=> $b->getStartTime(),
            SortDirection::StartDesc => static fn(JobExecution $a, JobExecution $b): int => $b->getStartTime() <=> $a->getStartTime(),
            SortDirection::EndAsc    => static fn(JobExecution $a, JobExecution $b): int => $a->getEndTime() <=> $b->getEndTime(),
            SortDirection::EndDesc   => static fn(JobExecution $a, JobExecution $b): int => $b->getEndTime() <=> $a->getEndTime(),
            default => null,
        };
        if ($order !== null) {
            \usort($executions, $order);
        }

        if ($ignoreLimit) {
            return \array_values($executions);
        }

        return \array_slice(\array_values($executions), $query->offset(), $query->limit());
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
