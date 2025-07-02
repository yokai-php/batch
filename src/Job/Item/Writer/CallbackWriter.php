<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Item\Writer;

use Yokai\Batch\Job\Item\ItemWriterInterface;
use Yokai\Batch\Job\JobExecutionAwareInterface;
use Yokai\Batch\Job\JobExecutionAwareTrait;

/**
 * An {@see ItemWriterInterface} that write items with a {@see Closure} provided at construction.
 *
 * Provided {@see Closure} must accept items to write and must return nothing.
 */
final class CallbackWriter implements ItemWriterInterface, JobExecutionAwareInterface
{
    use JobExecutionAwareTrait;

    public function __construct(
        private \Closure $callback,
    ) {
    }

    public function write(iterable $items): void
    {
        ($this->callback)($items, $this->jobExecution);
    }
}
