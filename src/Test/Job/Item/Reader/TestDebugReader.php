<?php

declare(strict_types=1);

namespace Yokai\Batch\Test\Job\Item\Reader;

use Yokai\Batch\Job\Item\ItemReaderInterface;
use Yokai\Batch\Test\Job\Item\TestDebugComponent;

/**
 * This {@see ItemReaderInterface} should be used in test
 * for components working with generic {@see ItemReaderInterface}.
 * It provides convenient assertion methods to ensure your reader was used correctly.
 */
final class TestDebugReader extends TestDebugComponent implements ItemReaderInterface
{
    private ItemReaderInterface $decorated;
    private bool $read = false;

    public function __construct(ItemReaderInterface $decorated)
    {
        parent::__construct($decorated);
        $this->decorated = $decorated;
    }

    /**
     * @inheritdoc
     */
    public function initialize(): void
    {
        $this->read = false;
        parent::initialize();
    }

    /**
     * @inheritdoc
     */
    public function read(): iterable
    {
        $this->read = true;

        return $this->decorated->read();
    }

    protected function wasUsed(): bool
    {
        return $this->read;
    }
}
