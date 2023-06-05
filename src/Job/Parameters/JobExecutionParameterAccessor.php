<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Parameters;

use Yokai\Batch\Exception\CannotAccessParameterException;
use Yokai\Batch\Exception\UndefinedJobParameterException;
use Yokai\Batch\JobExecution;

/**
 * This job parameter accessor implementation access parameters
 * through the contextual JobExecution parameter.
 */
final class JobExecutionParameterAccessor implements JobParameterAccessorInterface
{
    public function __construct(
        private string $name,
    ) {
    }

    public function get(JobExecution $execution): mixed
    {
        try {
            return $execution->getParameter($this->name);
        } catch (UndefinedJobParameterException $exception) {
            throw new CannotAccessParameterException(
                \sprintf('Cannot access "%s" parameter from job execution.', $this->name),
                $exception
            );
        }
    }
}
