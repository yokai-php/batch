<?php declare(strict_types=1);

namespace Yokai\Batch;

use ArrayIterator;
use Countable;
use IteratorAggregate;

final class Summary implements
    Countable,
    IteratorAggregate
{
    /**
     * @var array
     */
    private $values = [];

    /**
     * @param array $values
     */
    public function __construct(array $values = [])
    {
        $this->values = $values;
    }

    /**
     * @param string $key
     * @param mixed  $info
     */
    public function set(string $key, $info): void
    {
        $this->values[$key] = $info;
    }

    /**
     * @param string    $key
     * @param int|float $increment
     */
    public function increment(string $key, $increment = 1): void
    {
        $this->values[$key] = ($this->values[$key] ?? 0) + $increment;
    }

    /**
     * @param string $key
     *
     * @return mixed|null
     */
    public function get(string $key)
    {
        return $this->values[$key] ?? null;
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return $this->values;
    }

    /**
     */
    public function clear(): void
    {
        $this->values = [];
    }

    /**
     * @inheritdoc
     */
    public function getIterator()
    {
        return new ArrayIterator($this->values);
    }

    /**
     * @inheritdoc
     */
    public function count()
    {
        return count($this->values);
    }
}
