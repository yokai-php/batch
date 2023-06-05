<?php

declare(strict_types=1);

namespace Yokai\Batch\Test\Factory;

use Yokai\Batch\Factory\JobExecutionIdGeneratorInterface;

/**
 * This {@see JobExecutionIdGeneratorInterface} should be used in test to generate predictable ids.
 * Sequence is provided at construction and items will be
 */
final class SequenceJobExecutionIdGenerator implements JobExecutionIdGeneratorInterface
{
    /**
     * @phpstan-var list<string>
     */
    private array $sequence;
    private int $current = 0;

    /**
     * @phpstan-param list<string> $sequence
     */
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
