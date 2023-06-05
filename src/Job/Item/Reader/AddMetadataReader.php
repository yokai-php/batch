<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Item\Reader;

use Generator;
use Yokai\Batch\Exception\UnexpectedValueException;
use Yokai\Batch\Job\Item\AbstractElementDecorator;
use Yokai\Batch\Job\Item\ItemReaderInterface;

/**
 * This {@see ItemReaderInterface} decorates another reader.
 * The decorated reader must return array items.
 * This reader will add the data provided as constructor argument to each item.
 */
final class AddMetadataReader extends AbstractElementDecorator implements ItemReaderInterface
{
    public function __construct(
        private ItemReaderInterface $reader,
        /**
         * @phpstan-var array<string, mixed>
         */
        private array $metadata,
    ) {
    }

    /**
     * @phpstan-return Generator<array<mixed>>
     */
    public function read(): Generator
    {
        foreach ($this->reader->read() as $item) {
            if (!\is_array($item)) {
                throw UnexpectedValueException::type('array', $item);
            }

            yield $this->metadata + $item;
        }
    }

    protected function getDecoratedElements(): iterable
    {
        yield $this->reader;
    }
}
