<?php

declare(strict_types=1);

namespace Yokai\Batch\Storage;

/**
 * A job execution having this interface tells the developers it should be setuped before being used.
 */
interface SetupableJobExecutionStorageInterface
{
    /**
     * Setup the storage.
     */
    public function setup(): void;
}
