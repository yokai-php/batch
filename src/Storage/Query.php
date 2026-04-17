<?php

declare(strict_types=1);

namespace Yokai\Batch\Storage;

use Yokai\Batch\BatchStatus;
use Yokai\Batch\JobExecution;

/**
 * Query {@see JobExecution} list.
 * Passed as only argument of {@see QueryableJobExecutionStorageInterface::query} method.
 */
final readonly class Query
{
    public function __construct(
        /**
         * @var string[]
         */
        private array $jobs,
        /**
         * @var string[]
         */
        private array $ids,
        /**
         * @var BatchStatus[]
         */
        private array $statuses,
        private TimeFilter|null $startTime,
        private TimeFilter|null $endTime,
        private SortDirection|null $sort,
        private int $limit,
        private int $offset,
    ) {
    }

    /**
     * @return string[]
     */
    public function jobs(): array
    {
        return $this->jobs;
    }

    /**
     * @return string[]
     */
    public function ids(): array
    {
        return $this->ids;
    }

    /**
     * @return BatchStatus[]
     */
    public function statuses(): array
    {
        return $this->statuses;
    }

    public function startTime(): TimeFilter|null
    {
        return $this->startTime;
    }

    public function endTime(): TimeFilter|null
    {
        return $this->endTime;
    }

    public function sort(): SortDirection|null
    {
        return $this->sort;
    }

    public function limit(): int
    {
        return $this->limit;
    }

    public function offset(): int
    {
        return $this->offset;
    }
}
