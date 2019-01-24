<?php declare(strict_types=1);

namespace Yokai\Batch\Exception;

use DomainException;
use Throwable;

class CannotStoreJobExecutionException extends DomainException
{
    /**
     * @param string         $jobInstanceName
     * @param string         $id
     * @param Throwable|null $previous
     */
    public function __construct(string $jobInstanceName, string $id, Throwable $previous = null)
    {
        parent::__construct(
            sprintf('Cannot store job execution "%s" of job "%s"', $id, $jobInstanceName),
            0,
            $previous
        );
    }
}
