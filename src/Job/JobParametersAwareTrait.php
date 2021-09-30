<?php

declare(strict_types=1);

namespace Yokai\Batch\Job;

use Yokai\Batch\JobParameters;

/**
 * Covers {@see JobParametersAwareInterface}.
 */
trait JobParametersAwareTrait
{
    private JobParameters $jobParameters;

    /**
     * @inheritdoc
     */
    public function setJobParameters(JobParameters $jobParameters): void
    {
        $this->jobParameters = $jobParameters;
    }
}
