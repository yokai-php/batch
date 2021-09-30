<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Item;

/**
 * This interface might be used by any {@see ItemReaderInterface},
 * {@see ItemProcessorInterface} or {@see ItemWriterInterface}.
 * A class implementing this interface will be called by {@see ItemJob}
 * at the beginning of the execution.
 */
interface InitializableInterface
{
    /**
     * Custom logic on job initialization.
     */
    public function initialize(): void;
}
