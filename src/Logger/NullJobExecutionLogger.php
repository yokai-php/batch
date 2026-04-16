<?php

declare(strict_types=1);

namespace Yokai\Batch\Logger;

use Psr\Log\NullLogger;

/**
 * A no-op {@see JobExecutionLoggerInterface} implementation that discards all log messages.
 *
 * Useful when logging is handled entirely by an external system and
 * no in-memory or file-based log accumulation is desired.
 */
final class NullJobExecutionLogger extends NullLogger implements JobExecutionLoggerInterface
{
    public function getReference(): string
    {
        return '';
    }

    public function getLogs(): iterable
    {
        return [];
    }

    public function getLogsContent(): string
    {
        return '';
    }
}
