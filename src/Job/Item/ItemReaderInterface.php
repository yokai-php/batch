<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Item;

interface ItemReaderInterface
{
    /**
     * @return iterable
     */
    public function read(): iterable;
}
