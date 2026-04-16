<?php

declare(strict_types=1);

namespace Yokai\Batch\Logger;

use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use Stringable;
use Yokai\Batch\Event\PostExecuteEvent;
use Yokai\Batch\Event\PreExecuteEvent;

/**
 * A logger that you can reference in service of your application.
 * It will try to log into the {@see JobExecutionLoggerInterface} if a job is running.
 */
final class YokaiBatchLogger extends AbstractLogger
{
    private LoggerInterface|null $logger = null;

    /**
     * When a job is about to start: access and remember the logger
     */
    public function onPreExecute(PreExecuteEvent $event): void
    {
        $this->logger = $event->getExecution()->getLogger();
    }

    /**
     * When a job just finished: forget the logger.
     */
    public function onPostExecute(PostExecuteEvent $event): void
    {
        $this->logger = null;
    }

    public function log($level, Stringable|string $message, array $context = []): void
    {
        $this->logger?->log($level, $message, $context);
    }
}
