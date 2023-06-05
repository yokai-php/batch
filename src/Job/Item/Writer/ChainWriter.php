<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Item\Writer;

use Yokai\Batch\Job\Item\AbstractElementDecorator;
use Yokai\Batch\Job\Item\ItemWriterInterface;

/**
 * This {@see ItemWriterInterface} writes to multiple other writers.
 */
final class ChainWriter extends AbstractElementDecorator implements ItemWriterInterface
{
    public function __construct(
        /**
         * @var iterable<ItemWriterInterface> $writers
         */
        private iterable $writers,
    ) {
    }

    public function write(iterable $items): void
    {
        /** @var ItemWriterInterface $writer */
        foreach ($this->writers as $writer) {
            $writer->write($items);
        }
    }

    protected function getDecoratedElements(): iterable
    {
        return $this->writers;
    }
}
