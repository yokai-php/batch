<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Item;

interface ItemReaderInterface
{
    /**
     * @return iterable
     * @phpstan-return iterable<mixed>
     */
    public function read(): iterable;
}
