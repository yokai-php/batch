<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Item\Reader;

use Yokai\Batch\Job\Item\ItemReaderInterface;

/**
 * This {@see ItemReaderInterface} reads from items provided as constructor argument.
 */
final class StaticIterableReader implements ItemReaderInterface
{
    public function __construct(
        /**
         * @phpstan-var iterable<mixed>
         */
        private iterable $items,
    ) {
    }

    public function read(): iterable
    {
        return $this->items;
    }
}
