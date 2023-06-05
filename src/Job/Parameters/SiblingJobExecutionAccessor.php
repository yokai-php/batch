<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Parameters;

use Yokai\Batch\Exception\CannotAccessParameterException;
use Yokai\Batch\JobExecution;

/**
 * This job parameter accessor implementation decorates an other implementation
 * but passes a named sibling job execution instead of provided execution.
 */
final class SiblingJobExecutionAccessor implements JobParameterAccessorInterface
{
    public function __construct(
        private JobParameterAccessorInterface $accessor,
        private string $sibling,
    ) {
    }

    public function get(JobExecution $execution): mixed
    {
        $parent = $execution->getParentExecution();
        if ($parent === null) {
            throw new CannotAccessParameterException('Cannot access parameter, job execution has no parent.');
        }
        $sibling = $parent->getChildExecution($this->sibling);
        if ($sibling === null) {
            throw new CannotAccessParameterException(
                \sprintf('Cannot access parameter, job execution has no sibling named "%s".', $this->sibling)
            );
        }

        return $this->accessor->get($sibling);
    }
}
