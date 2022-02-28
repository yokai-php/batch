<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Item\Processor;

use Yokai\Batch\Job\Item\ItemProcessorInterface;

/**
 * This {@see ItemProcessorInterface} perform no transformation.
 */
final class NullProcessor implements ItemProcessorInterface
{
    /**
     * @inheritDoc
     */
    public function process(mixed $item): mixed
    {
        return $item;
    }
}
