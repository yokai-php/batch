<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Item;

use Yokai\Batch\Job\Item\Exception\SkipItemException;

interface ItemProcessorInterface
{
    /**
     * @param mixed $item
     *
     * @return mixed
     * @throws SkipItemException
     */
    public function process($item);
}
