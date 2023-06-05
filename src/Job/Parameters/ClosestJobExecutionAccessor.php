<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Parameters;

use Yokai\Batch\Exception\CannotAccessParameterException;
use Yokai\Batch\JobExecution;

/**
 * This job parameter accessor implementation decorates an other implementation
 * but tries every job execution from the one provided to the root, returning the first that matches.
 */
final class ClosestJobExecutionAccessor implements JobParameterAccessorInterface
{
    public function __construct(
        private JobParameterAccessorInterface $accessor
    ) {
    }

    public function get(JobExecution $execution): mixed
    {
        $candidateExecution = $execution;
        do {
            try {
                return $this->accessor->get($candidateExecution);
            } catch (CannotAccessParameterException) {
                $candidateExecution = $candidateExecution->getParentExecution();
            }
        } while ($candidateExecution !== null);

        throw new CannotAccessParameterException('Cannot access parameter from any job execution in ancestors.');
    }
}
