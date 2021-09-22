<?php

declare(strict_types=1);

namespace Yokai\Batch\Test\Job\Item\Writer;

use Yokai\Batch\Job\Item\InitializableInterface;
use Yokai\Batch\Job\Item\ItemWriterInterface;

final class InMemoryWriter implements ItemWriterInterface, InitializableInterface
{
    /**
     * @phpstan-var list<mixed>
     */
    private array $items = [];

    /**
     * @phpstan-var list<list<mixed>>
     */
    private array $batchItems = [];

    /**
     * @inheritdoc
     */
    public function initialize(): void
    {
        $this->items = [];
    }

    /**
     * @inheritdoc
     */
    public function write(iterable $items): void
    {
        $batch = [];
        foreach ($items as $item) {
            $this->items[] = $item;
            $batch[] = $item;
        }
        $this->batchItems[] = $batch;
    }

    /**
     * @phpstan-return list<mixed>
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @phpstan-return list<list<mixed>>
     */
    public function getBatchItems(): array
    {
        return $this->batchItems;
    }
}
