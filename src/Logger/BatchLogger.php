<?php

declare(strict_types=1);

namespace Yokai\Batch\Logger;

use Psr\Log\AbstractLogger;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Stringable;
use Yokai\Batch\Event\PostExecuteEvent;
use Yokai\Batch\Event\PreExecuteEvent;

/**
 * BatchLogger allow to log with the jobExecutionLogger
 */
class BatchLogger extends AbstractLogger
{
    private ?LoggerInterface $batchLogger = null;

    /**
     * Access and remember the logger
     */
    public function onPreExecute(PreExecuteEvent $event): void
    {
        $this->batchLogger = $event->getExecution()->getLogger();
    }

    /**
     * Forget the logger
     */
    public function onPostExecute(PostExecuteEvent $event): void
    {
        $this->batchLogger = null;
    }

    /**
     * Log with the batchLogger defined in the PreExecuteEvent or with nullLogger if nothing remembered
     *
     * @param array<string, mixed> $context
     * @throws InvalidArgumentException
     */
    public function log($level, Stringable|string $message, array $context = []): void
    {
        ($this->batchLogger ?? new NullLogger())->log($level, $message, $context);
    }
}
