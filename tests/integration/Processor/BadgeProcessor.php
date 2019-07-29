<?php

namespace Yokai\Batch\Tests\Integration\Processor;

use Yokai\Batch\Job\Item\ItemProcessorInterface;
use Yokai\Batch\Tests\Integration\Entity\Badge;

final class BadgeProcessor implements ItemProcessorInterface
{
    public function process($item)
    {
        $badge = new Badge();
        $badge->label = $item['label'];
        $badge->rank = $item['rank'];

        return $badge;
    }
}
