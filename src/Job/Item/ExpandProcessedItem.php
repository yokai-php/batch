<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Item;

use ArrayIterator;
use Iterator;
use IteratorAggregate;
use IteratorIterator;

/**
 * A processor may return an element of this class
 * in order to write multiple items per item read.
 *
 * @template-implements IteratorAggregate<mixed>
 */
final class ExpandProcessedItem implements IteratorAggregate
{
    /**
     * @phpstan-var Iterator<mixed>
     */
    private Iterator $iterator;

    /**
     * @phpstan-param iterable<mixed> $iterator
     */
    public function __construct(iterable $iterator)
    {
        if (\is_array($iterator)) {
            $this->iterator = new ArrayIterator($iterator);
        } else {
            $this->iterator = new IteratorIterator($iterator);
        }
    }

    /**
     * @phpstan-return Iterator<mixed>
     */
    public function getIterator(): Iterator
    {
        return $this->iterator;
    }
}
