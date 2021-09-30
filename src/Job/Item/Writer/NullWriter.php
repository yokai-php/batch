<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Item\Writer;

use Yokai\Batch\Job\Item\ItemWriterInterface;

/**
 * This {@see ItemWriterInterface} writes nothing.
 */
final class NullWriter implements ItemWriterInterface
{
    /**
     * @inheritdoc
     */
    public function write(iterable $items): void
    {
    }
}
