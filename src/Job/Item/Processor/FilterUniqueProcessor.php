<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Item\Processor;

use Closure;
use Yokai\Batch\Job\Item\Exception\SkipItemException;
use Yokai\Batch\Job\Item\ItemProcessorInterface;

/**
 * This {@see ItemProcessorInterface} will skip every item considered as already encountered.
 *
 * To determine whether the item was already encountered,
 * it will use a {@see Closure} that will be called for each item
 * and that will be responsible for extracting an identifier from the item.
 */
final class FilterUniqueProcessor implements ItemProcessorInterface
{
    private Closure $extractUnique;

    /**
     * @phpstan-var array<string, bool>
     */
    private array $encountered = [];

    public function __construct(Closure $extractUnique)
    {
        $this->extractUnique = $extractUnique;
    }

    /**
     * Uses item array value as unique value.
     *
     * Example, FilterUniqueProcessor::withArrayKey('name')
     * will use 'name' array index as unique value of each item.
     */
    public static function withArrayKey(string $key): self
    {
        return new self(fn(array $item) => $item[$key]);
    }

    /**
     * Uses object property value as the item index.
     *
     * Example, FilterUniqueProcessor::withProperty('name')
     * will use '->name' object property as unique value of each item.
     */
    public static function withProperty(string $property): self
    {
        return new self(fn(object $item) => $item->$property);
    }

    /**
     * Uses object method return value as the item index.
     *
     * Example, FilterUniqueProcessor::withProperty('getName')
     * will use 'getName()' method result as unique value of each item.
     */
    public static function withGetter(string $getter): self
    {
        return new self(fn(object $item) => $item->$getter());
    }

    /**
     * @inheritDoc
     */
    public function process($item)
    {
        $unique = ($this->extractUnique)($item);
        if (isset($this->encountered[$unique])) {
            throw SkipItemException::justSkip($item, ['unique' => $unique]);
        }

        $this->encountered[$unique] = true;

        return $item;
    }
}
