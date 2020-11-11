<?php

declare(strict_types=1);

namespace Yokai\Batch\Storage;

use Yokai\Batch\BatchStatus;
use Yokai\Batch\Exception\UnexpectedValueException;

final class QueryBuilder
{
    private const SORTS_ENUM = [
        Query::SORT_BY_START_ASC,
        Query::SORT_BY_START_DESC,
        Query::SORT_BY_END_ASC,
        Query::SORT_BY_END_DESC,
    ];

    private const STATUSES_ENUM = [
        BatchStatus::PENDING,
        BatchStatus::RUNNING,
        BatchStatus::STOPPED,
        BatchStatus::COMPLETED,
        BatchStatus::ABANDONED,
        BatchStatus::FAILED,
    ];

    /**
     * @var string[]
     */
    private $jobNames = [];

    /**
     * @var string[]
     */
    private $ids = [];

    /**
     * @var int[]
     */
    private $statuses = [];

    /**
     * @var string|null
     */
    private $sortBy = null;

    /**
     * @var int
     */
    private $limit = 10;

    /**
     * @var int
     */
    private $offset = 0;

    public function jobs(array $names): self
    {
        $names = array_unique($names);
        foreach ($names as $name) {
            if (!is_string($name)) {
                throw UnexpectedValueException::type('string', $name);
            }
        }

        $this->jobNames = $names;

        return $this;
    }

    public function ids(array $ids): self
    {
        $ids = array_unique($ids);
        foreach ($ids as $id) {
            if (!is_string($id)) {
                throw UnexpectedValueException::type('string', $id);
            }
        }

        $this->ids = $ids;

        return $this;
    }

    public function statuses(array $statuses): self
    {
        $statuses = array_unique($statuses);
        foreach ($statuses as $status) {
            if (!in_array($status, self::STATUSES_ENUM, true)) {
                throw UnexpectedValueException::enum(self::STATUSES_ENUM, $status);
            }
        }

        $this->statuses = $statuses;

        return $this;
    }

    public function sort(string $by): self
    {
        if (!in_array($by, self::SORTS_ENUM, true)) {
            throw UnexpectedValueException::enum(self::SORTS_ENUM, $by);
        }

        $this->sortBy = $by;

        return $this;
    }

    public function limit(int $limit, int $offset): self
    {
        if ($limit <= 0) {
            throw UnexpectedValueException::range(0, null, $limit);
        }
        if ($offset < 0) {
            throw UnexpectedValueException::range(0, null, $limit);
        }

        $this->limit = $limit;
        $this->offset = $offset;

        return $this;
    }

    public function getQuery(): Query
    {
        return new Query(
            $this->jobNames,
            $this->ids,
            $this->statuses,
            $this->sortBy,
            $this->limit,
            $this->offset
        );
    }
}
