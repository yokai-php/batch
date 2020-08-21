<?php

declare(strict_types=1);

namespace Yokai\Batch;

use DateTime;
use DateTimeZone;
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

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
     * @var DateTimeZone|null
     */
    private static $timezone;

    /**
     * @var JobExecutionLogs
     */
    private $logs;

    public function __construct(JobExecutionLogs $logs)
    {
        $this->logs = $logs;
    }

    /**
     * @inheritDoc
     */
    public function log($level, $message, array $context = []): void
    {
        $this->logs->log(
            sprintf(
                '[%s] %s: %s %s',
                $this->date(),
                self::LEVELS[$level] ?? '',
                $message,
                json_encode($context)
            )
        );
    }

    private function date(): string
    {
        if (!static::$timezone) {
            static::$timezone = new DateTimeZone(date_default_timezone_get() ?: 'UTC');
        }

        $date = new DateTime('now', static::$timezone);
        $date->setTimezone(static::$timezone);

        return $date->format('Y-m-d H:i:s');
    }
}
