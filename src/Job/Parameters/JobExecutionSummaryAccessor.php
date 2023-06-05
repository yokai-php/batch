<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Parameters;

use Yokai\Batch\Exception\CannotAccessParameterException;
use Yokai\Batch\JobExecution;

/**
 * This job parameter accessor implementation access summary
 * through the contextual JobExecution parameter.
 */
final class JobExecutionSummaryAccessor implements JobParameterAccessorInterface
{
    public function __construct(
        private string $name,
    ) {
    }

    public function get(JobExecution $execution): mixed
    {
        if (!$execution->getSummary()->has($this->name)) {
            throw new CannotAccessParameterException(
                \sprintf('Cannot access parameter, summary variable "%s" does not exists.', $this->name)
            );
        }

        return $execution->getSummary()->get($this->name);
    }
}
