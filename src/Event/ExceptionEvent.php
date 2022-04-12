<?php

declare(strict_types=1);

namespace Yokai\Batch\Event;

use Throwable;
use Yokai\Batch\BatchStatus;
use Yokai\Batch\Job\JobExecutor;
use Yokai\Batch\Job\JobInterface;
use Yokai\Batch\JobExecution;

/**
 * This event is triggered by {@see JobExecutor}
 * whenever an Exception is thrown during {@see JobInterface::execute}.
 *
 * This event can be used to control {@see JobExecution::$status} when Exception occurs.
 */
final class ExceptionEvent extends JobEvent
{
    private int $status = BatchStatus::FAILED;

    public function __construct(
        JobExecution $execution,
        private Throwable $exception,
    ) {
        parent::__construct($execution);
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    public function getException(): Throwable
    {
        return $this->exception;
    }
}
