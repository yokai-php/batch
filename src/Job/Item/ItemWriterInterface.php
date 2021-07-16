<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Item;

interface ItemWriterInterface
{
    /**
     * @param iterable $items
     * @phpstan-param iterable<mixed> $items
     */
    public function write(iterable $items): void;
}
