<?php

declare(strict_types=1);

namespace Yokai\Batch\Factory\JobExecutionLoggerFactory;

use Yokai\Batch\Factory\JobExecutionLoggerFactoryInterface;
use Yokai\Batch\Logger\JobExecutionLoggerInterface;
use Yokai\Batch\Logger\NullJobExecutionLogger;

/**
 * A {@see JobExecutionLoggerFactoryInterface} implementation that always discards logs.
 *
 * Useful when logging is handled entirely by an external system and no log accumulation is desired.
 */
final class NullJobExecutionLoggerFactory implements JobExecutionLoggerFactoryInterface
{
    public function create(string $jobExecutionId): JobExecutionLoggerInterface
    {
        return new NullJobExecutionLogger();
    }

    public function restore(string $logsReference): JobExecutionLoggerInterface
    {
        return new NullJobExecutionLogger();
    }
}
