<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Item;

/**
 * The item reader is responsible for fetching items in {@see ItemJob}.
 */
interface ItemReaderInterface
{
    /**
     * A list of items to process and write.
     *
     * @return iterable
     * @phpstan-return iterable<mixed>
     */
    public function read(): iterable;
}
