<?php

declare(strict_types=1);

namespace Yokai\Batch\Job;

use Yokai\Batch\JobParameters;

trait JobParametersAwareTrait
{
    /**
     * @var JobParameters
     */
    private $jobParameters;

    /**
     * @param JobParameters $jobParameters
     */
    public function setJobParameters(JobParameters $jobParameters): void
    {
        $this->jobParameters = $jobParameters;
    }
}
