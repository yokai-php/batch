<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Item\Reader;

use Yokai\Batch\Job\Item\ItemReaderInterface;

final class StaticIterableReader implements ItemReaderInterface
{
    /**
     * @var iterable
     */
    private iterable $items;

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
