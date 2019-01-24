<?php declare(strict_types=1);

namespace Yokai\Batch\Job\Item\Processor;

use Yokai\Batch\Job\Item\ItemProcessorInterface;

final class NullProcessor implements ItemProcessorInterface
{
    /**
     * @inheritDoc
     */
    public function process($item)
    {
        return $item;
    }
}
