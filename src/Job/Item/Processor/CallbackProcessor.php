<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Item\Processor;

use Closure;
use Yokai\Batch\Job\Item\ItemProcessorInterface;
use Yokai\Batch\Job\JobExecutionAwareInterface;
use Yokai\Batch\Job\JobExecutionAwareTrait;

/**
 * This {@see ItemProcessorInterface} will transform every item
 * with a closure provided at object's construction.
 */
final class CallbackProcessor implements ItemProcessorInterface, JobExecutionAwareInterface
{
    use JobExecutionAwareTrait;

    public function __construct(
        private Closure $callback,
    ) {
    }

    public function process(mixed $item): mixed
    {
        return ($this->callback)($item, $this->jobExecution);
    }
}
