<?php

declare(strict_types=1);

namespace Yokai\Batch;

/**
 * Stores all logs related to a {@see JobExecution}.
 */
final class JobExecutionLogs
{
    /**
     * Logs content.
     */
    private string $logs;

    public function __construct(string $logs = '')
    {
        $this->logs = $logs;
    }

    public function __toString(): string
    {
        return $this->logs;
    }

    public function log(string $message): void
    {
        $this->logs .= $message . PHP_EOL;
    }
}
