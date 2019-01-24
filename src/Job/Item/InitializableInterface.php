<?php declare(strict_types=1);

namespace Yokai\Batch\Job\Item;

interface InitializableInterface
{
    /**
     * Custom logic on job initialization.
     */
    public function initialize(): void;
}
