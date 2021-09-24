<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Job;

use Yokai\Batch\Job\JobExecutionAwareInterface;
use Yokai\Batch\Job\JobExecutionAwareTrait;
use Yokai\Batch\Job\JobParametersAwareInterface;
use Yokai\Batch\Job\JobParametersAwareTrait;
use Yokai\Batch\Job\SummaryAwareInterface;
use Yokai\Batch\Job\SummaryAwareTrait;
use Yokai\Batch\JobExecution;
use Yokai\Batch\JobParameters;
use Yokai\Batch\Summary;

final class ConfigurableElement implements
    JobExecutionAwareInterface,
    JobParametersAwareInterface,
    SummaryAwareInterface
{
    use JobExecutionAwareTrait;
    use JobParametersAwareTrait;
    use SummaryAwareTrait;

    public function getJobExecution(): JobExecution
    {
        return $this->jobExecution;
    }

    public function getJobParameters(): JobParameters
    {
        return $this->jobParameters;
    }

    public function getSummary(): Summary
    {
        return $this->summary;
    }
}
