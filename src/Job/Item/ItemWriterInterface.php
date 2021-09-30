<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Item;

/**
 * The item writer is responsible for writing transformed items in {@see ItemJob}.
 */
interface ItemWriterInterface
{
    /**
     * Writes items.
     *
     * @param iterable $items A batch of items to write
     * @phpstan-param iterable<mixed> $items
     */
    public function write(iterable $items): void;
}
