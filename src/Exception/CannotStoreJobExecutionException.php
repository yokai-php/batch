<?php

declare(strict_types=1);

namespace Yokai\Batch\Exception;

use Throwable;

class CannotStoreJobExecutionException extends RuntimeException
{
    public function __construct(string $jobName, string $executionId, Throwable $previous = null)
    {
        parent::__construct(
            sprintf('Cannot store job execution "%s" of job "%s"', $executionId, $jobName),
            $previous
        );
    }
}
