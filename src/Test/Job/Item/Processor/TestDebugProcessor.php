<?php

declare(strict_types=1);

namespace Yokai\Batch\Test\Job\Item\Processor;

use Yokai\Batch\Job\Item\ItemProcessorInterface;
use Yokai\Batch\Test\Job\Item\TestDebugComponent;

final class TestDebugProcessor extends TestDebugComponent implements ItemProcessorInterface
{
    private ItemProcessorInterface $decorated;
    private bool $processed = false;

    public function __construct(ItemProcessorInterface $decorated)
    {
        parent::__construct($decorated);
        $this->decorated = $decorated;
    }

    public function initialize(): void
    {
        $this->processed = false;
        parent::initialize();
    }

    public function process(mixed $item): mixed
    {
        $this->processed = true;

        return $this->decorated->process($item);
    }

    protected function wasUsed(): bool
    {
        return $this->processed;
    }
}
