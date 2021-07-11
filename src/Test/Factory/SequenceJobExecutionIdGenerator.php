<?php

declare(strict_types=1);

namespace Yokai\Batch\Test\Factory;

use Yokai\Batch\Factory\JobExecutionIdGeneratorInterface;

final class SequenceJobExecutionIdGenerator implements JobExecutionIdGeneratorInterface
{
    private array $sequence;
    private int $current = 0;

    public function __construct(array $sequence)
    {
        $this->sequence = \array_values($sequence);
    }

    public function generate(): string
    {
        $current = $this->sequence[$this->current] ?? '';
        $this->current++;

        return (string)$current;
    }
}
