<?php

declare(strict_types=1);

namespace Yokai\Batch\Test\Job\Item\Writer;

use Yokai\Batch\Job\Item\ItemWriterInterface;
use Yokai\Batch\Test\Job\Item\TestDebugComponent;

/**
 * This {@see ItemWriterInterface} should be used in test
 * for components working with generic {@see ItemWriterInterface}.
 * It provides convenient assertion methods to ensure your writer was used correctly.
 */
final class TestDebugWriter extends TestDebugComponent implements ItemWriterInterface
{
    private ItemWriterInterface $decorated;
    private bool $written = false;

    public function __construct(ItemWriterInterface $decorated)
    {
        parent::__construct($decorated);
        $this->decorated = $decorated;
    }

    public function initialize(): void
    {
        $this->written = false;
        parent::initialize();
    }

    public function write(iterable $items): void
    {
        $this->written = true;
        $this->decorated->write($items);
    }

    protected function wasUsed(): bool
    {
        return $this->written;
    }
}
