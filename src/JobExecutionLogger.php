<?php

declare(strict_types=1);

namespace Yokai\Batch;

use DateTime;
use DateTimeZone;
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

/**
 * This logger is designed to log into a {@see JobExecutionLogger} object,
 * that is attached to the {@see JobExecution::$logs} property.
 */
final class JobExecutionLogger extends AbstractLogger
{
    private const LEVELS = [
        LogLevel::DEBUG => 'DEBUG',
        LogLevel::INFO => 'INFO',
        LogLevel::NOTICE => 'NOTICE',
        LogLevel::WARNING => 'WARNING',
        LogLevel::ERROR => 'ERROR',
        LogLevel::CRITICAL => 'CRITICAL',
        LogLevel::ALERT => 'ALERT',
        LogLevel::EMERGENCY => 'EMERGENCY',
    ];

    /**
     * Current timezone used for date formatting.
     */
    private static ?DateTimeZone $timezone = null;

    public function __construct(
        /**
         * Logs container : where logs are actually written.
         */
        private JobExecutionLogs $logs,
    ) {
    }

    /**
     * @param array<string, mixed> $context
     */
    public function log($level, $message, array $context = []): void
    {
        $this->logs->log(
            sprintf(
                '[%s] %s: %s %s',
                $this->date(),
                self::LEVELS[$level] ?? '',
                $message,
                json_encode($context, JSON_THROW_ON_ERROR)
            )
        );
    }

    private function date(): string
    {
        self::$timezone ??= new DateTimeZone(date_default_timezone_get() ?: 'UTC');

        $date = new DateTime('now', self::$timezone);
        $date->setTimezone(self::$timezone);

        return $date->format('Y-m-d H:i:s');
    }
}
