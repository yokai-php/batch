<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Item;

use Yokai\Batch\Job\Item\Exception\SkipItemException;

/**
 * The item processor is responsible for transforming every read items in {@see ItemJob}.
 */
interface ItemProcessorInterface
{
    /**
     * Transform the item before writing.
     *
     * @param mixed $item The item read
     *
     * @return mixed The item transformed
     * @throws SkipItemException If the item should be skipped
     */
    public function process(mixed $item): mixed;
}
