<?php

declare(strict_types=1);

namespace Yokai\Batch\Factory\JobExecutionLoggerFactory;

use Yokai\Batch\Factory\JobExecutionLoggerFactoryInterface;
use Yokai\Batch\Logger\InMemoryJobExecutionLogger;
use Yokai\Batch\Logger\JobExecutionLoggerInterface;

/**
 * Default {@see JobExecutionLoggerFactoryInterface} implementation.
 *
 * Creates an {@see InMemoryJobExecutionLogger}, preserving the behaviour that existed before the logger became pluggable.
 */
final class InMemoryJobExecutionLoggerFactory implements JobExecutionLoggerFactoryInterface
{
    public function create(string $jobExecutionId): JobExecutionLoggerInterface
    {
        return new InMemoryJobExecutionLogger();
    }

    public function restore(string $logsReference): JobExecutionLoggerInterface
    {
        return new InMemoryJobExecutionLogger($logsReference);
    }
}
