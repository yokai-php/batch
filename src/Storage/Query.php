<?php

declare(strict_types=1);

namespace Yokai\Batch\Storage;

final class Query
{
    public const SORT_BY_START_ASC = 'start_asc';
    public const SORT_BY_START_DESC = 'start_desc';
    public const SORT_BY_END_ASC = 'end_asc';
    public const SORT_BY_END_DESC = 'end_desc';

    /**
     * @var string[]
     */
    private $jobNames;

    /**
     * @var string[]
     */
    private $ids;

    /**
     * @var int[]
     */
    private $statuses;

    /**
     * @var string|null
     */
    private $sortBy;

    /**
     * @var int
     */
    private $limit;

    /**
     * @var int
     */
    private $offset;

    /**
     * @internal
     */
    public function __construct(
        array $jobNames,
        array $ids,
        array $statuses,
        ?string $sortBy,
        int $limit,
        int $offset = 0
    ) {
        $this->jobNames = $jobNames;
        $this->ids = $ids;
        $this->statuses = $statuses;
        $this->sortBy = $sortBy;
        $this->limit = $limit;
        $this->offset = $offset;
    }

    public function jobs(): array
    {
        return $this->jobNames;
    }

    public function ids(): array
    {
        return $this->ids;
    }

    public function statuses(): array
    {
        return $this->statuses;
    }

    public function sort(): ?string
    {
        return $this->sortBy;
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
