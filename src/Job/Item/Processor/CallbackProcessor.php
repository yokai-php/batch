<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Item\Processor;

use Closure;
use Yokai\Batch\Job\Item\ItemProcessorInterface;

final class CallbackProcessor implements ItemProcessorInterface
{
    private Closure $callback;

    public function __construct(Closure $callback)
    {
        $this->callback = $callback;
    }

    public function process(mixed $item): mixed
    {
        return ($this->callback)($item);
    }
}
