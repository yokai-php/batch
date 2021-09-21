<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Item\Reader;

use Closure;
use Yokai\Batch\Job\Item\ElementConfiguratorTrait;
use Yokai\Batch\Job\Item\FlushableInterface;
use Yokai\Batch\Job\Item\InitializableInterface;
use Yokai\Batch\Job\Item\ItemReaderInterface;
use Yokai\Batch\Job\JobExecutionAwareInterface;
use Yokai\Batch\Job\JobExecutionAwareTrait;

/**
 * An {@see ItemReaderInterface} that decorates another {@see ItemReaderInterface}
 * and extract item index of each item using a {@see Closure}.
 *
 * Provided {@see Closure} must accept a single argument (the read item)
 * and must return a value (preferably unique) that will be item index.
 */
final class IndexWithReader implements
    ItemReaderInterface,
    InitializableInterface,
    FlushableInterface,
    JobExecutionAwareInterface
{
    use ElementConfiguratorTrait;
    use JobExecutionAwareTrait;

    private ItemReaderInterface $reader;
    private Closure $extractItemIndex;

    public function __construct(ItemReaderInterface $reader, Closure $extractItemIndex)
    {
        $this->reader = $reader;
        $this->extractItemIndex = $extractItemIndex;
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

    /**
     * @inheritdoc
     */
    public function initialize(): void
    {
        $this->configureElementJobContext($this->reader, $this->jobExecution);
        $this->initializeElement($this->reader);
    }

    /**
     * @inheritdoc
     */
    public function read(): iterable
    {
        foreach ($this->reader->read() as $item) {
            yield ($this->extractItemIndex)($item) => $item;
        }
    }

    /**
     * @inheritdoc
     */
    public function flush(): void
    {
        $this->flushElement($this->reader);
    }
}
