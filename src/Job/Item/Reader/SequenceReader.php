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
    public function __construct(
        /**
         * @var iterable<ItemReaderInterface> $readers
         */
        private iterable $readers,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function read(): iterable
    {
        /** @var ItemReaderInterface $reader */
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
