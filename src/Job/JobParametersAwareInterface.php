<?php

declare(strict_types=1);

namespace Yokai\Batch\Job;

use Yokai\Batch\JobExecution;
use Yokai\Batch\JobParameters;

/**
 * A class implementing this interface will gain access
 * to {@see JobParameters} of the current {@see JobExecution}.
 *
 * Parameters can also be accessed by implementing {@see JobExecutionAwareInterface}
 * and calling {@see JobExecution::getParameters} on the provided execution.
 *
 * Default implementation from {@see JobParametersAwareTrait} can be used.
 */
interface JobParametersAwareInterface
{
    /**
     * Set parameters to the job component.
     */
    public function setJobParameters(JobParameters $parameters): void;
}
