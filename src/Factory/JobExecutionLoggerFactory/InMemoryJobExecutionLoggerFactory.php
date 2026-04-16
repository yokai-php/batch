<?php

declare(strict_types=1);

namespace Yokai\Batch\Factory\JobExecutionLoggerFactory;

use Yokai\Batch\Factory\JobExecutionLoggerFactoryInterface;
use Yokai\Batch\JobExecutionLogs;
use Yokai\Batch\Logger\InMemoryJobExecutionLogger;
use Yokai\Batch\Logger\JobExecutionLoggerInterface;

/**
 * Default {@see JobExecutionLoggerFactoryInterface} implementation.
 *
 * Creates a {@see InMemoryJobExecutionLogger} backed by an in-memory {@see JobExecutionLogs} string.
 * This preserves the behaviour that existed before the logger became pluggable.
 */
final class InMemoryJobExecutionLoggerFactory implements JobExecutionLoggerFactoryInterface
{
    public function create(): JobExecutionLoggerInterface
    {
        return new InMemoryJobExecutionLogger();
    }

    public function restore(string $logsReference): JobExecutionLoggerInterface
    {
        return new InMemoryJobExecutionLogger($logsReference);
    }
}
