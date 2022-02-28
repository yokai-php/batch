<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Item\Processor;

use Yokai\Batch\Job\Item\AbstractElementDecorator;
use Yokai\Batch\Job\Item\ItemProcessorInterface;

/**
 * This {@see ItemProcessorInterface} owns a collection of processors
 * and call each processor one after the other, providing previous result to the next processor.
 * If you are familiar with the middleware architecture, it is very much alike.
 */
final class ChainProcessor extends AbstractElementDecorator implements ItemProcessorInterface
{
    public function __construct(
        /**
         * @var iterable<ItemProcessorInterface> $processors
         */
        private iterable $processors,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function process(mixed $item): mixed
    {
        /** @var ItemProcessorInterface $processor */
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
