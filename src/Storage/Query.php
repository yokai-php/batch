<?php

declare(strict_types=1);

namespace Yokai\Batch\Storage;

use Yokai\Batch\JobExecution;

/**
 * Query {@see JobExecution} list.
 * Passed as only argument of {@see QueryableJobExecutionStorageInterface::query} method.
 */
final class Query
{
    public const SORT_BY_START_ASC = 'start_asc';
    public const SORT_BY_START_DESC = 'start_desc';
    public const SORT_BY_END_ASC = 'end_asc';
    public const SORT_BY_END_DESC = 'end_desc';

    /**
     * @internal Do not use directly, use {@see QueryBuilder} instead.
     */
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
         * @var int[]
         */
        private array $statuses,
        private ?TimeFilter $startTime,
        private ?TimeFilter $endTime,
        private ?string $sort,
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
     * @return int[]
     */
    public function statuses(): array
    {
        return $this->statuses;
    }

    public function startTime(): ?TimeFilter
    {
        return $this->startTime;
    }

    public function endTime(): ?TimeFilter
    {
        return $this->endTime;
    }

    public function sort(): ?string
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
