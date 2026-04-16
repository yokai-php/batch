<?php

declare(strict_types=1);

namespace Yokai\Batch\Factory;

use Yokai\Batch\Logger\JobExecutionLoggerInterface;

/**
 * Manages {@see JobExecutionLoggerInterface} instances for the full lifecycle of a job execution:
 * creation of new executions and restoration of existing ones from storage.
 *
 * Inject a custom implementation to control how logs are collected and where they are stored
 * (in-memory, file, database…).
 */
interface JobExecutionLoggerFactoryInterface
{
    /**
     * Creates a fresh logger for a new job execution.
     */
    public function create(): JobExecutionLoggerInterface;

    /**
     * Restores a logger from a previously stored reference.
     * Used when deserializing a {@see \Yokai\Batch\JobExecution} from storage.
     *
     * @param string $logsReference The value previously returned by {@see JobExecutionLoggerInterface::getReference()}
     */
    public function restore(string $logsReference): JobExecutionLoggerInterface;
}
