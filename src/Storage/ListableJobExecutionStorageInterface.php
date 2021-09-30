<?php

declare(strict_types=1);

namespace Yokai\Batch\Storage;

use Yokai\Batch\JobExecution;

/**
 * Fetch a list of all {@see JobExecution}, all from the same job.
 */
interface ListableJobExecutionStorageInterface extends JobExecutionStorageInterface
{
    /**
     * @return iterable|JobExecution[]
     */
    public function list(string $jobName): iterable;
}
