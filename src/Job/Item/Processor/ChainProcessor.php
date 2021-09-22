<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Item\Processor;

use Yokai\Batch\Job\Item\AbstractElementDecorator;
use Yokai\Batch\Job\Item\ItemProcessorInterface;

final class ChainProcessor extends AbstractElementDecorator implements ItemProcessorInterface
{
    /**
     * @var iterable|ItemProcessorInterface[]
     */
    private iterable $processors;

    /**
     * @param iterable|ItemProcessorInterface[] $processors
     */
    public function __construct(iterable $processors)
    {
        $this->processors = $processors;
    }

    /**
     * @inheritDoc
     */
    public function process($item)
    {
        foreach ($this->processors as $processor) {
            $item = $processor->process($item);
        }

        return $item;
    }

    /**
     * @inheritDoc
     */
    protected function getDecoratedElements(): iterable
    {
        return $this->processors;
    }
}
