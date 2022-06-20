<?php

declare(strict_types=1);

namespace Yokai\Batch;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Yokai\Batch\Exception\UnexpectedValueException;
use Yokai\Batch\Job\SummaryAwareInterface;

/**
 * The summary contains arbitrary values that a developer
 * might want to store along with the {@see JobExecution}.
 * Usually this is about analysis/debug information.
 *
 * You can obtain the summary by implementing {@see SummaryAwareInterface}.
 *
 * @template-implements IteratorAggregate<string, mixed>
 */
final class Summary implements
    Countable,
    IteratorAggregate
{
    public function __construct(
        /**
         * @phpstan-var array<string, mixed>
         */
        private array $values = [],
    ) {
    }

    /**
     * Set value.
     */
    public function set(string $key, mixed $info): void
    {
        $this->values[$key] = $info;
    }

    /**
     * Handle a numeric value by incrementing value of it.
     */
    public function increment(string $key, float|int $increment = 1): void
    {
        $this->values[$key] = ($this->values[$key] ?? 0) + $increment;
    }

    /**
     * Handle an array value by appending a new value to it.
     */
    public function append(string $key, mixed $value): void
    {
        $this->values[$key] ??= [];
        if (!\is_array($this->values[$key])) {
            throw UnexpectedValueException::type('array', $this->values[$key]);
        }

        $this->values[$key][] = $value;
    }

    /**
     * Get a value, or null if not set.
     */
    public function get(string $key): mixed
    {
        return $this->values[$key] ?? null;
    }

    /**
     * Whether a value was set.
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->values);
    }

    /**
     * Get all values.
     *
     * @phpstan-return array<string, mixed>
     */
    public function all(): array
    {
        return $this->values;
    }

    /**
     * Clear all values.
     */
    public function clear(): void
    {
        $this->values = [];
    }

    /**
     * @inheritdoc
     * @phpstan-return ArrayIterator<string, mixed>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->values);
    }

    /**
     * @inheritdoc
     */
    public function count(): int
    {
        return count($this->values);
    }
}
