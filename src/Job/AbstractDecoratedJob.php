<?php

declare(strict_types=1);

namespace Yokai\Batch\Job;

use Yokai\Batch\JobExecution;

/**
 * This {@see JobInterface} is designed to be extended in your project.
 * It decorates another {@see JobInterface} that will actually run the code.
 * It might be used as a "constructor helper".
 */
abstract class AbstractDecoratedJob implements JobInterface
{
    public function __construct(
        private JobInterface $job,
    ) {
    }

    final public function execute(JobExecution $jobExecution): void
    {
        $this->preExecute($jobExecution);
        $this->job->execute($jobExecution);
        $this->postExecute($jobExecution);
    }

    /**
     * Overrides this method if you want to do something before the job is executed.
     */
    protected function preExecute(JobExecution $jobExecution): void
    {
    }

    /**
     * Overrides this method if you want to do something after the job is executed.
     */
    protected function postExecute(JobExecution $jobExecution): void
    {
    }
}
