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
     * @param iterable<mixed> $items A batch of items to write
     */
    public function write(iterable $items): void;
}
