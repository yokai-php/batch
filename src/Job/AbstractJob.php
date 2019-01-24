<?php declare(strict_types=1);

namespace Yokai\Batch\Job;

use Throwable;
use Yokai\Batch\BatchStatus;
use Yokai\Batch\JobExecution;

abstract class AbstractJob implements JobInterface
{
    /**
     * @inheritDoc
     */
    public function execute(JobExecution $jobExecution): void
    {
        if (!$jobExecution->getStatus()->isExecutable()) {
            //todo this is not a normal state here, maybe it is a good idea to add a log or something
            return;
        }

        $jobExecution->setStartTime(new \DateTime());
        $jobExecution->setStatus(BatchStatus::RUNNING);

        $status = BatchStatus::COMPLETED;

        try {
            $this->doExecute($jobExecution);
        } catch (Throwable $exception) {
            $status = $this->getStatusForException($exception);
            $jobExecution->addFailureException($exception);
        }

        $jobExecution->setEndTime(new \DateTime());
        $jobExecution->setStatus($status);
    }

    /**
     * @param JobExecution $jobExecution
     */
    abstract protected function doExecute(JobExecution $jobExecution): void;

    /**
     * @param Throwable $exception
     *
     * @return int
     */
    protected function getStatusForException(Throwable $exception): int
    {
        return BatchStatus::FAILED;
    }
}
