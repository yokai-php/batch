<?php

declare(strict_types=1);

namespace Yokai\Batch\Logger;

use DateTime;
use DateTimeZone;
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;
use Yokai\Batch\JobExecutionLogs;

/**
 * Default {@see JobExecutionLoggerInterface} implementation.
 *
 * Formats log messages and accumulates them in an in-memory {@see JobExecutionLogs} string.
 * Use {@see NullJobExecutionLogger} to discard all logs, or implement
 * {@see JobExecutionLoggerInterface} yourself to write to a file, database, etc.
 */
final class InMemoryJobExecutionLogger extends AbstractLogger implements JobExecutionLoggerInterface
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
    private static DateTimeZone|null $timezone = null;

    public function __construct(
        private string $logs = '',
    ) {
    }

    public function log($level, string|\Stringable $message, array $context = []): void
    {
        $this->logs .= \sprintf(
            '[%s] %s: %s %s',
            $this->date(),
            self::LEVELS[$level] ?? '',
            $message,
            \json_encode($context, JSON_THROW_ON_ERROR),
        ) . \PHP_EOL;
    }

    public function getReference(): string
    {
        return $this->logs;
    }

    public function getLogs(): iterable
    {
        $content = $this->logs;
        if ($content === '') {
            return;
        }

        yield from \explode(\PHP_EOL, \rtrim($content, \PHP_EOL));
    }

    public function getLogsContent(): string
    {
        return $this->logs;
    }

    private function date(): string
    {
        self::$timezone ??= new DateTimeZone(\date_default_timezone_get() ?: 'UTC');

        $date = new DateTime('now', self::$timezone);
        $date->setTimezone(self::$timezone);

        return $date->format('Y-m-d H:i:s');
    }
}
