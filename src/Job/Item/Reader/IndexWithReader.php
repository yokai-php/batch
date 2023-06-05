<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Item\Reader;

use Closure;
use Yokai\Batch\Job\Item\AbstractElementDecorator;
use Yokai\Batch\Job\Item\ItemReaderInterface;

/**
 * An {@see ItemReaderInterface} that decorates another {@see ItemReaderInterface}
 * and extract item index of each item using a {@see Closure}.
 *
 * Provided {@see Closure} must accept a single argument (the read item)
 * and must return a value (preferably unique) that will be item index.
 */
final class IndexWithReader extends AbstractElementDecorator implements ItemReaderInterface
{
    public function __construct(
        private ItemReaderInterface $reader,
        private Closure $extractItemIndex,
    ) {
    }

    /**
     * Uses item array value as the item index.
     *
     * Example, IndexWithReader::withArrayKey(..., 'name')
     * will use 'name' array index of each read item as the item index.
     */
    public static function withArrayKey(ItemReaderInterface $reader, string $key): self
    {
        return new self($reader, fn(array $item) => $item[$key]);
    }

    /**
     * Uses object property value as the item index.
     *
     * Example, IndexWithReader::withProperty(..., 'name')
     * will use 'name' object property of each read item as the item index.
     */
    public static function withProperty(ItemReaderInterface $reader, string $property): self
    {
        return new self($reader, fn(object $item) => $item->$property);
    }

    /**
     * Uses object method return value as the item index.
     *
     * Example, IndexWithReader::withProperty(..., 'getName')
     * will call 'getName()' method of each read item and uses the result as the item index.
     */
    public static function withGetter(ItemReaderInterface $reader, string $getter): self
    {
        return new self($reader, fn(object $item) => $item->$getter());
    }

    public function read(): iterable
    {
        foreach ($this->reader->read() as $item) {
            yield ($this->extractItemIndex)($item) => $item;
        }
    }

    protected function getDecoratedElements(): iterable
    {
        yield $this->reader;
    }
}
