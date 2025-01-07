<?php

declare(strict_types=1);

namespace Yokai\Batch\Storage;

use DateTimeInterface;
use Yokai\Batch\Exception\UnexpectedValueException;

/**
 * DTO with optional time boundaries.
 */
final class TimeFilter
{
    public function __construct(
        private ?DateTimeInterface $from,
        private ?DateTimeInterface $to,
    ) {
        if ($from !== null && $to !== null && $from > $to) {
            throw new UnexpectedValueException('TimeFilter expect "from" boundary to be lower than "to" boundary.');
        }
    }

    public function getFrom(): ?DateTimeInterface
    {
        return $this->from;
    }

    public function getTo(): ?DateTimeInterface
    {
        return $this->to;
    }
}
