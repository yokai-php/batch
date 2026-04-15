<?php

declare(strict_types=1);

namespace Yokai\Batch\Storage;

use DateTimeInterface;
use Yokai\Batch\Exception\UnexpectedValueException;

/**
 * DTO with optional time boundaries.
 */
final readonly class TimeFilter
{
    public function __construct(
        private DateTimeInterface|null $from,
        private DateTimeInterface|null $to,
    ) {
        if ($from !== null && $to !== null && $from > $to) {
            throw new UnexpectedValueException('TimeFilter expect "from" boundary to be lower than "to" boundary.');
        }
    }

    public function getFrom(): DateTimeInterface|null
    {
        return $this->from;
    }

    public function getTo(): DateTimeInterface|null
    {
        return $this->to;
    }
}
