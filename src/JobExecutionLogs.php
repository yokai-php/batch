<?php

declare(strict_types=1);

namespace Yokai\Batch;

/**
 * Stores all logs related to a {@see JobExecution}.
 */
final class JobExecutionLogs implements \Stringable
{
    public function __construct(
        /**
         * Logs content.
         */
        private string $logs = '',
    ) {
    }

    public function __toString(): string
    {
        return $this->logs;
    }

    /**
     * Append message to logs.
     */
    public function log(string $message): void
    {
        $this->logs .= $message . PHP_EOL;
    }
}
