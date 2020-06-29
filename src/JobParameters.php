<?php

declare(strict_types=1);

namespace Yokai\Batch;

use ArrayIterator;
use Countable;
use Iterator;
use IteratorAggregate;
use Yokai\Batch\Exception\UndefinedJobParameterException;

final class JobParameters implements
    Countable,
    IteratorAggregate
{
    /**
     * @var array
     */
    private $parameters;

    /**
     * @param array $parameters
     */
    public function __construct(array $parameters = [])
    {
        $this->parameters = $parameters;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function has(string $name): bool
    {
        return array_key_exists($name, $this->parameters);
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function get(string $name)
    {
        if (!$this->has($name)) {
            throw new UndefinedJobParameterException($name);
        }

        return $this->parameters[$name];
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return count($this->parameters);
    }

    /**
     * @inheritDoc
     */
    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->parameters);
    }
}
