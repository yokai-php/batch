<?php

declare(strict_types=1);

namespace Yokai\Batch\Storage;

use Yokai\Batch\JobExecution;

interface ListableJobExecutionStorageInterface extends JobExecutionStorageInterface
{
    /**
     * @param string $jobName
     *
     * @return iterable|JobExecution[]
     */
    public function list(string $jobName): iterable;
}
