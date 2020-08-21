<?php

declare(strict_types=1);

namespace Yokai\Batch\Job;

use Yokai\Batch\JobExecution;

trait JobExecutionAwareTrait
{
    /**
     * @var JobExecution
     */
    private $jobExecution;

    public function setJobExecution(JobExecution $jobExecution): void
    {
        $this->jobExecution = $jobExecution;
    }

    public function getRootExecution(): JobExecution
    {
        return $this->jobExecution->getRootExecution();
    }
}
