<?php

declare(strict_types=1);

namespace Yokai\Batch\Storage;

use DateTimeInterface;
use Yokai\Batch\BatchStatus;
use Yokai\Batch\Exception\UnexpectedValueException;

/**
 * Fluent interface for building {@see Query}.
 *
 * Usage:
 *
 *     (new QueryBuilder())
 *         ->ids(['123', '456'])
 *         ->jobs(['export', 'import'])
 *         ->statuses([BatchStatus::Running, BatchStatus::Completed])
 *         ->startTime(new \DateTimeImmutable('2023-07-07 15:18'), new \DateTime('2023-07-07 16:30'))
 *         ->endTime(new \DateTimeImmutable('2023-07-07 15:18'), new \DateTime('2023-07-07 16:30'))
 *         ->sort(SortDirection::EndDesc)
 *         ->limit(6, 12)
 *         ->getQuery();
 *
 * Not an immutable object, can be used without chaining calls:
 *
 *     $builder = new QueryBuilder();
 *     $builder->ids(['123', '456']);
 *     $builder->jobs(['export', 'import']);
 *     $builder->statuses([BatchStatus::Running, BatchStatus::Completed]);
 *     $builder->startTime(new \DateTimeImmutable('2023-07-07 15:18'), new \DateTime('2023-07-07 16:30'));
 *     $builder->endTime(new \DateTimeImmutable('2023-07-07 15:18'), new \DateTime('2023-07-07 16:30'));
 *     $builder->sort(SortDirection::EndDesc);
 *     $builder->limit(6, 12);
 *     $builder->getQuery();
 */
final class QueryBuilder
{
    /**
     * @var string[]
     */
    private array $jobNames = [];

    /**
     * @var string[]
     */
    private array $ids = [];

    /**
     * @var BatchStatus[]
     */
    private array $statuses = [];

    private TimeFilter|null $startTime = null;

    private TimeFilter|null $endTime = null;

    private SortDirection|null $sortBy = null;

    private int $limit = 10;

    private int $offset = 0;

    /**
     * Filter executions that are for one of the given job names.
     *
     * @param string[] $names
     */
    public function jobs(array $names): self
    {
        $names = \array_unique($names);
        foreach ($names as $name) {
            if (!\is_string($name)) {
                throw UnexpectedValueException::type('string', $name);
            }
        }

        $this->jobNames = $names;

        return $this;
    }

    /**
     * Filter executions that are one of given ids.
     *
     * @param string[] $ids
     */
    public function ids(array $ids): self
    {
        $ids = \array_unique($ids);
        foreach ($ids as $id) {
            if (!\is_string($id)) {
                throw UnexpectedValueException::type('string', $id);
            }
        }

        $this->ids = $ids;

        return $this;
    }

    /**
     * Filter executions that are on given status.
     *
     * @param BatchStatus[] $statuses
     */
    public function statuses(array $statuses): self
    {
        foreach ($statuses as $status) {
            if (!$status instanceof BatchStatus) {
                throw UnexpectedValueException::type(BatchStatus::class, $status);
            }
        }

        $this->statuses = \array_unique($statuses, \SORT_REGULAR);

        return $this;
    }

    /**
     * Filter executions that started in a frame.
     * Both frame boundaries are optional.
     * Calling this method with both null boundaries result in removing the filter.
     *
     * @param DateTimeInterface|null $from Beginning of the time frame
     * @param DateTimeInterface|null $to   End of the time frame
     */
    public function startTime(DateTimeInterface|null $from, DateTimeInterface|null $to): self
    {
        if ($from === null && $to === null) {
            $this->startTime = null;
        } else {
            $this->startTime = new TimeFilter($from, $to);
        }

        return $this;
    }

    /**
     * Filter executions that ended in a frame.
     * Both frame boundaries are optional.
     * Calling this method with both null boundaries result in removing the filter.
     *
     * @param DateTimeInterface|null $from Beginning of the time frame
     * @param DateTimeInterface|null $to   End of the time frame
     */
    public function endTime(DateTimeInterface|null $from, DateTimeInterface|null $to): self
    {
        if ($from === null && $to === null) {
            $this->endTime = null;
        } else {
            $this->endTime = new TimeFilter($from, $to);
        }

        return $this;
    }

    /**
     * Sort executions.
     */
    public function sort(SortDirection $by): self
    {
        $this->sortBy = $by;

        return $this;
    }

    /**
     * Limit query to a certain amount of executions.
     */
    public function limit(int $limit, int $offset): self
    {
        if ($limit < 1) {
            throw UnexpectedValueException::min(1, $limit);
        }
        if ($offset < 0) {
            throw UnexpectedValueException::min(0, $offset);
        }

        $this->limit = $limit;
        $this->offset = $offset;

        return $this;
    }

    /**
     * Build query from criteria in this builder.
     */
    public function getQuery(): Query
    {
        return new Query(
            $this->jobNames,
            $this->ids,
            $this->statuses,
            $this->startTime,
            $this->endTime,
            $this->sortBy,
            $this->limit,
            $this->offset,
        );
    }
}
