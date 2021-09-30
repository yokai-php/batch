<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Item;

/**
 * This interface might be used by any {@see ItemReaderInterface},
 * {@see ItemProcessorInterface} or {@see ItemWriterInterface}.
 * A class implementing this interface will be called by {@see ItemJob}
 * at the end of the execution.
 */
interface FlushableInterface
{
    /**
     * Custom logic on job completion.
     */
    public function flush(): void;
}
