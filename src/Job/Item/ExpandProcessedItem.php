<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Item;

use ArrayIterator;
use IteratorIterator;

/**
 * A processor may return an element of this class
 * in order to write multiple items per item read.
 */
final class ExpandProcessedItem extends IteratorIterator
{
    public function __construct(iterable $iterator, string $class = '')
    {
        if (\is_array($iterator)) {
            $iterator = new ArrayIterator($iterator);
        }
        parent::__construct($iterator, $class);
    }
}
