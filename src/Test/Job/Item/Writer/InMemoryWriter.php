<?php

declare(strict_types=1);

namespace Yokai\Batch\Test\Job\Item\Writer;

use Yokai\Batch\Job\Item\InitializableInterface;
use Yokai\Batch\Job\Item\ItemWriterInterface;

final class InMemoryWriter implements ItemWriterInterface, InitializableInterface
{
    private array $items = [];

    public function initialize(): void
    {
        $this->items = [];
    }

    public function write(iterable $items): void
    {
        foreach ($items as $item) {
            $this->items[] = $item;
        }
    }

    public function getItems(): array
    {
        return $this->items;
    }
}
