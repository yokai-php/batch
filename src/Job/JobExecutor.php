<?php

declare(strict_types=1);

namespace Yokai\Batch\Job;

use DateTimeImmutable;
use Psr\EventDispatcher\EventDispatcherInterface;
use Throwable;
use Yokai\Batch\BatchStatus;
use Yokai\Batch\Event\ExceptionEvent;
use Yokai\Batch\Event\PostExecuteEvent;
use Yokai\Batch\Event\PreExecuteEvent;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Registry\JobRegistry;
use Yokai\Batch\Storage\JobExecutionStorageInterface;

/**
 * This class is responsible for every job's execution.
 *
 * It contains convenient wrapper around actual execution, for instance
 * - status check to avoid executing something that is not executable
 * - exception handling during execution
 * - lifecycle events during execution
 * - JobExecution storage before and after execution
 */
final class JobExecutor
{
    public function __construct(
        private JobRegistry $jobRegistry,
        private JobExecutionStorageInterface $jobExecutionStorage,
        private ?EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function execute(JobExecution $jobExecution): void
    {
        $logger = $jobExecution->getLogger();
        $rootExecution = $jobExecution->getRootExecution();
        $name = $jobExecution->getJobName();

        if (!$jobExecution->getStatus()->isExecutable()) {
            $logger->warning('Job execution not allowed to be executed', ['job' => $name]);

            return;
        }

        $logger->debug('Starting job', ['job' => $name]);

        // Considering async execution monitoring,
        // JobExecution needs to be stored before job is actually executed,
        // right after setting start time and status.
        $jobExecution->setStartTime(new DateTimeImmutable());
        $jobExecution->setStatus(BatchStatus::RUNNING);
        $this->jobExecutionStorage->store($rootExecution);

        $this->eventDispatcher?->dispatch(new PreExecuteEvent($jobExecution));

        $status = BatchStatus::COMPLETED;

        try {
            $this->jobRegistry->get($name)->execute($jobExecution);
        } catch (Throwable $exception) {
            $event = new ExceptionEvent($jobExecution, $exception);
            $this->eventDispatcher?->dispatch($event);
            $status = $event->getStatus();
            $jobExecution->addFailureException($exception);
        }

        $jobExecution->setEndTime(new DateTimeImmutable());
        $jobExecution->setStatus($status);

        $duration = $jobExecution->getDuration()->format('%Hh %Im %Ss');
        if ($jobExecution->getStatus()->isSuccessful()) {
            $logger->info('Job executed successfully', ['job' => $name, 'duration' => $duration]);
        } else {
            $logger->error('Job did not executed successfully', ['job' => $name, 'duration' => $duration]);
        }

        $summary = $jobExecution->getSummary()->all();
        if (\count($summary) > 0) {
            $logger->debug('Job produced summary', \array_merge(['job' => $name], $summary));
        }

        $this->jobExecutionStorage->store($rootExecution);

        $this->eventDispatcher?->dispatch(new PostExecuteEvent($jobExecution));
    }
}
