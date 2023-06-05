<?php

declare(strict_types=1);

namespace Yokai\Batch\Test\Job\Item\Writer;

use Yokai\Batch\Job\Item\InitializableInterface;
use Yokai\Batch\Job\Item\ItemWriterInterface;

/**
 * This {@see ItemWriterInterface} should be used in test
 * for components working with generic {@see ItemWriterInterface}.
 * It provides convenient methods retrieve written items along execution
 * and perform assertions on these.
 */
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

    public function initialize(): void
    {
        $this->items = [];
    }

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
