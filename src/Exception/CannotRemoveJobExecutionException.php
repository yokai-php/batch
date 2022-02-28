<?php

declare(strict_types=1);

namespace Yokai\Batch\Exception;

use Throwable;

class CannotRemoveJobExecutionException extends RuntimeException
{
    public function __construct(string $jobName, string $executionId, Throwable $previous = null)
    {
        parent::__construct(
            sprintf('Cannot remove job execution "%s" of job "%s"', $executionId, $jobName),
            $previous
        );
    }
}
