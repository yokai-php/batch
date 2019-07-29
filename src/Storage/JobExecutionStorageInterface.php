<?php declare(strict_types=1);

namespace Yokai\Batch\Storage;

use Yokai\Batch\Exception\CannotStoreJobExecutionException;
use Yokai\Batch\Exception\JobExecutionNotFoundException;
use Yokai\Batch\JobExecution;

interface JobExecutionStorageInterface
{
    /**
     * @param JobExecution $execution
     *
     * @throws CannotStoreJobExecutionException
     */
    public function store(JobExecution $execution): void;

    /**
     * @param string $jobName
     * @param string $executionId
     *
     * @return JobExecution
     * @throws JobExecutionNotFoundException
     */
    public function retrieve(string $jobName, string $executionId): JobExecution;
}
