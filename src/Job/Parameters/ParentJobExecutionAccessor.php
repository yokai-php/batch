<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Parameters;

use Yokai\Batch\Exception\CannotAccessParameterException;
use Yokai\Batch\JobExecution;

/**
 * This job parameter accessor implementation decorates an other implementation
 * but tries with parent job execution of the one provided.
 */
final class ParentJobExecutionAccessor implements JobParameterAccessorInterface
{
    public function __construct(
        private JobParameterAccessorInterface $accessor,
    ) {
    }

    /**
     * @inheritdoc
     */
    public function get(JobExecution $execution): mixed
    {
        $parent = $execution->getParentExecution();
        if ($parent === null) {
            throw new CannotAccessParameterException('Cannot access parameter, job execution has no parent.');
        }

        return $this->accessor->get($parent);
    }
}
