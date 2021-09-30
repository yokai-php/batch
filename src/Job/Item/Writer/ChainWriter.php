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
    /**
     * @var iterable|ItemWriterInterface[]
     */
    private iterable $writers;

    /**
     * @param iterable|ItemWriterInterface[] $writers
     */
    public function __construct(iterable $writers)
    {
        $this->writers = $writers;
    }

    /**
     * @inheritDoc
     */
    public function write(iterable $items): void
    {
        foreach ($this->writers as $writer) {
            $writer->write($items);
        }
    }

    /**
     * @inheritdoc
     */
    protected function getDecoratedElements(): iterable
    {
        return $this->writers;
    }
}
