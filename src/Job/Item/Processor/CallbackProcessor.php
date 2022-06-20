<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Item\Processor;

use Closure;
use Yokai\Batch\Job\Item\ItemProcessorInterface;

/**
 * This {@see ItemProcessorInterface} will transform every item
 * with a closure provided at object's construction.
 */
final class CallbackProcessor implements ItemProcessorInterface
{
    public function __construct(
        private Closure $callback,
    ) {
    }

    public function process(mixed $item): mixed
    {
        return ($this->callback)($item);
    }
}
