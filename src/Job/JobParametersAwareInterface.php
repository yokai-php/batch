<?php declare(strict_types=1);

namespace Yokai\Batch\Job;

use Yokai\Batch\JobParameters;

interface JobParametersAwareInterface
{
    /**
     * @param JobParameters $parameters
     */
    public function setJobParameters(JobParameters $parameters): void;
}
