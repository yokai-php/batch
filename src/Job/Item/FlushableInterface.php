<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Item;

interface FlushableInterface
{
    /**
     * Custom logic on job completion.
     */
    public function flush(): void;
}
