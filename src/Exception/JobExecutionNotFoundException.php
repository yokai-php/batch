<?php declare(strict_types=1);

namespace Yokai\Batch\Exception;

use DomainException;
use Throwable;

class JobExecutionNotFoundException extends DomainException
{
    /**
     * @param string         $jobInstanceName
     * @param string         $id
     * @param Throwable|null $previous
     */
    public function __construct(string $jobInstanceName, string $id, Throwable $previous = null)
    {
        parent::__construct(
            sprintf('Job execution "%s" of job "%s" cannot be found', $id, $jobInstanceName),
            0,
            $previous
        );
    }
}
