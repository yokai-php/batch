<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Item\Reader;

use Yokai\Batch\Job\Item\ItemReaderInterface;

final class StaticIterableReader implements ItemReaderInterface
{
    /**
     * @phpstan-var iterable<mixed>
     */
    private iterable $items;

    /**
     * @phpstan-param iterable<mixed> $items
     */
    public function __construct(iterable $items)
    {
        $this->items = $items;
    }

    /**
     * @inheritDoc
     */
    public function read(): iterable
    {
        return $this->items;
    }
}
