<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Parameters;

use Yokai\Batch\Exception\CannotAccessParameterException;
use Yokai\Batch\JobExecution;

/**
 * A job that is able to work with parameter can require in instance of this interface.
 * The job then safely rely on this instance to retrieve the parameter value.
 */
interface JobParameterAccessorInterface
{
    /**
     * @param JobExecution $execution A job execution (for context)
     *
     * @return mixed The requested value
     * @throws CannotAccessParameterException if the parameter cannot be accessed
     */
    public function get(JobExecution $execution): mixed;
}
