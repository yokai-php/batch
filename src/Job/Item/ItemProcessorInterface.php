<?php declare(strict_types=1);

namespace Yokai\Batch\Job\Item;

interface ItemProcessorInterface
{
    /**
     * @param mixed $item
     *
     * @return mixed
     * @throws InvalidItemException
     */
    public function process($item);
}
