<?php declare(strict_types=1);

namespace Yokai\Batch\Exception;

use DomainException;
use Throwable;

class JobExecutionNotFoundException extends DomainException
{
    /**
     * @param string         $jobName
     * @param string         $executionId
     * @param Throwable|null $previous
     */
    public function __construct(string $jobName, string $executionId, Throwable $previous = null)
    {
        parent::__construct(
            sprintf('Job execution "%s" of job "%s" cannot be found', $executionId, $jobName),
            0,
            $previous
        );
    }
}
