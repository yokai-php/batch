<?php

declare(strict_types=1);

namespace Yokai\Batch;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Yokai\Batch\Exception\UndefinedJobParameterException;
use Yokai\Batch\Job\JobParametersAwareInterface;

/**
 * Parameters provided to a {@see JobExecution} at trigger time.
 *
 * You can obtain the parameters by implementing {@see JobParametersAwareInterface}.
 *
 * @template-implements IteratorAggregate<string, mixed>
 */
final class JobParameters implements
    Countable,
    IteratorAggregate
{
    public function __construct(
        /**
         * @phpstan-var array<string, mixed>
         */
        private array $parameters = [],
    ) {
    }

    /**
     * Get all parameter values.
     *
     * @phpstan-return array<string, mixed>
     */
    public function all(): array
    {
        return $this->parameters;
    }

    /**
     * Whether a parameter is defined.
     */
    public function has(string $name): bool
    {
        return array_key_exists($name, $this->parameters);
    }

    /**
     * Get a parameter value.
     *
     * @throws UndefinedJobParameterException If parameter is not defined
     */
    public function get(string $name): mixed
    {
        if (!$this->has($name)) {
            throw new UndefinedJobParameterException($name);
        }

        return $this->parameters[$name];
    }

    public function count(): int
    {
        return count($this->parameters);
    }

    /**
     * @phpstan-return ArrayIterator<string, mixed>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->parameters);
    }
}
