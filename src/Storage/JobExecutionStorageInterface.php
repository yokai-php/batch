<?php

declare(strict_types=1);

namespace Yokai\Batch\Storage;

use Yokai\Batch\Exception\CannotRemoveJobExecutionException;
use Yokai\Batch\Exception\CannotStoreJobExecutionException;
use Yokai\Batch\Exception\JobExecutionNotFoundException;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Launcher\JobLauncherInterface;

/**
 * Whenever a job is executed, a {@see JobExecution} will be created.
 * The storage is responsible for adding a persistence layer for these job's execution.
 */
interface JobExecutionStorageInterface
{
    /**
     * Persist the execution on the storage.
     *
     * This method is used natively by the {@see JobLauncherInterface},
     * before and after a job's execution.
     *
     * @throws CannotStoreJobExecutionException
     */
    public function store(JobExecution $execution): void;

    /**
     * Remove the execution from the storage.
     *
     * This method is not used natively.
     *
     * @throws CannotRemoveJobExecutionException
     */
    public function remove(JobExecution $execution): void;

    /**
     * Retrieve an execution from the storage.
     *
     * This method is used natively by the {@see JobLauncherInterface},
     * before a job's execution when id is provided from the outside.
     *
     * @throws JobExecutionNotFoundException
     */
    public function retrieve(string $jobName, string $executionId): JobExecution;
}
