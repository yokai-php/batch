<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Item\Reader;

use Yokai\Batch\Job\Item\AbstractElementDecorator;
use Yokai\Batch\Job\Item\ItemReaderInterface;

/**
 * This {@see ItemReaderInterface} reads from multiple readers, one after the other.
 */
final class SequenceReader extends AbstractElementDecorator implements ItemReaderInterface
{
    /**
     * @var iterable|ItemReaderInterface[]
     */
    private iterable $readers;

    /**
     * @param iterable|ItemReaderInterface[] $readers
     */
    public function __construct(iterable $readers)
    {
        $this->readers = $readers;
    }

    /**
     * @inheritDoc
     */
    public function read(): iterable
    {
        foreach ($this->readers as $reader) {
            foreach ($reader->read() as $item) {
                yield $item;
            }
        }
    }

    /**
     * @inheritdoc
     */
    protected function getDecoratedElements(): iterable
    {
        return $this->readers;
    }
}
