<?php

namespace Yokai\Batch\Job\Item\Writer;

use Yokai\Batch\Job\Item\ItemWriterInterface;

final class NullWriter implements ItemWriterInterface
{
    /**
     * @inheritdoc
     */
    public function write(iterable $items): void
    {
    }
}
