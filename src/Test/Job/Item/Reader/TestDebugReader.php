<?php

declare(strict_types=1);

namespace Yokai\Batch\Test\Job\Item\Reader;

use Yokai\Batch\Job\Item\ItemReaderInterface;
use Yokai\Batch\Test\Job\Item\TestDebugComponent;

final class TestDebugReader extends TestDebugComponent implements ItemReaderInterface
{
    private ItemReaderInterface $decorated;
    private bool $read = false;

    public function __construct(ItemReaderInterface $decorated)
    {
        parent::__construct($decorated);
        $this->decorated = $decorated;
    }

    public function initialize(): void
    {
        $this->read = false;
        parent::initialize();
    }

    public function read(): iterable
    {
        $this->read = true;

        return $this->decorated->read();
    }

    public function wasRead(): bool
    {
        return $this->read;
    }
}
