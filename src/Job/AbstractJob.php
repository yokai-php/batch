<?php

declare(strict_types=1);

namespace Yokai\Batch\Job;

use Throwable;
use Yokai\Batch\BatchStatus;
use Yokai\Batch\JobExecution;

abstract class AbstractJob implements JobInterface
{
    /**
     * @inheritDoc
     */
    final public function execute(JobExecution $jobExecution): void
    {
        if (!$jobExecution->getStatus()->isExecutable()) {
            $jobExecution->getLogger()->error('Job is not executable', ['job' => $jobExecution->getJobName()]);

            return;
        }

        $jobExecution->setStartTime(new \DateTimeImmutable());
        $jobExecution->setStatus(BatchStatus::RUNNING);

        $status = BatchStatus::COMPLETED;

        try {
            $this->doExecute($jobExecution);
        } catch (Throwable $exception) {
            $status = $this->getStatusForException($exception);
            $jobExecution->addFailureException($exception);
        }

        $jobExecution->setEndTime(new \DateTimeImmutable());
        $jobExecution->setStatus($status);
    }

    abstract protected function doExecute(JobExecution $jobExecution): void;

    protected function getStatusForException(Throwable $exception): int
    {
        return BatchStatus::FAILED;
    }
}
