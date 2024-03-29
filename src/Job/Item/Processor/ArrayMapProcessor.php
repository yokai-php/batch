<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Item\Processor;

use Closure;
use Yokai\Batch\Exception\UnexpectedValueException;
use Yokai\Batch\Job\Item\ItemProcessorInterface;

/**
 * This {@see ItemProcessorInterface} calls {@see array_map} on each item.
 */
final class ArrayMapProcessor implements ItemProcessorInterface
{
    private Closure $callback;

    public function __construct(Closure $callback)
    {
        $this->callback = $callback;
    }

    /**
     * @phpstan-return array<int|string, mixed>
     */
    public function process(mixed $item): array
    {
        if (!\is_array($item)) {
            throw UnexpectedValueException::type('array', $item);
        }

        return \array_map($this->callback, $item);
    }
}
