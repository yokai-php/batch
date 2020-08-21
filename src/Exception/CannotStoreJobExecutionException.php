<?php

declare(strict_types=1);

namespace Yokai\Batch\Exception;

use DomainException;
use Throwable;

class CannotStoreJobExecutionException extends DomainException
{
    /**
     * @param string         $jobName
     * @param string         $executionId
     * @param Throwable|null $previous
     */
    public function __construct(string $jobName, string $executionId, Throwable $previous = null)
    {
        parent::__construct(
            sprintf('Cannot store job execution "%s" of job "%s"', $executionId, $jobName),
            0,
            $previous
        );
    }
}
