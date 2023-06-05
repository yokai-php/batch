<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Parameters;

use Yokai\Batch\JobExecution;

/**
 * This job parameter accessor implementation decorates an other implementation
 * but passes root job execution instead of provided execution.
 */
final class RootJobExecutionAccessor implements JobParameterAccessorInterface
{
    public function __construct(
        private JobParameterAccessorInterface $accessor,
    ) {
    }

    public function get(JobExecution $execution): mixed
    {
        return $this->accessor->get($execution->getRootExecution());
    }
}
