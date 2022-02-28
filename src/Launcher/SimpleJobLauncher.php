<?php

declare(strict_types=1);

namespace Yokai\Batch\Launcher;

use Psr\EventDispatcher\EventDispatcherInterface;
use Throwable;
use Yokai\Batch\BatchStatus;
use Yokai\Batch\Event\PostExecuteEvent;
use Yokai\Batch\Event\PreExecuteEvent;
use Yokai\Batch\Exception\JobExecutionNotFoundException;
use Yokai\Batch\Factory\JobExecutionFactory;
use Yokai\Batch\Job\JobInterface;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Registry\JobRegistry;
use Yokai\Batch\Storage\JobExecutionStorageInterface;

/**
 * This {@see JobLauncherInterface} executes all job directly in the same PHP process.
 * Eventually, all launcher implementation ends with calling this one.
 */
class SimpleJobLauncher implements JobLauncherInterface
{
    public function __construct(
        private JobRegistry $jobRegistry,
        private JobExecutionFactory $jobExecutionFactory,
        private JobExecutionStorageInterface $jobExecutionStorage,
        private ?EventDispatcherInterface $eventDispatcher,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function launch(string $name, array $configuration = []): JobExecution
    {
        $job = $this->jobRegistry->get($name);
        $jobExecution = $this->getJobExecution($name, $configuration);
        $logger = $jobExecution->getLogger();

        if (!$jobExecution->getStatus()->isExecutable()) {
            $logger->warning('Job execution not allowed to be executed', ['job' => $name]);

            return $jobExecution;
        }

        $logger->debug('Starting job', ['job' => $name]);

        $this->dispatch(new PreExecuteEvent($jobExecution));

        $this->execute($job, $jobExecution);

        if ($jobExecution->getStatus()->isSuccessful()) {
            $logger->info('Job executed successfully', ['job' => $name]);
        } else {
            $logger->error('Job did not executed successfully', ['job' => $name]);
        }

        $this->jobExecutionStorage->store($jobExecution);

        $this->dispatch(new PostExecuteEvent($jobExecution));

        return $jobExecution;
    }

    private function execute(JobInterface $job, JobExecution $execution): void
    {
        try {
            $job->execute($execution);
        } catch (Throwable $exception) {
            $execution->setStatus(BatchStatus::FAILED);
            $execution->addFailureException($exception);
        }
    }

    /**
     * @phpstan-param array<string, mixed> $configuration
     */
    private function getJobExecution(string $name, array $configuration): JobExecution
    {
        $id = $configuration['_id'] ?? null;
        if (is_string($id)) {
            try {
                return $this->jobExecutionStorage->retrieve($name, $id);
            } catch (JobExecutionNotFoundException) {
            }
        }

        $jobExecution = $this->jobExecutionFactory->create($name, $configuration);
        $this->jobExecutionStorage->store($jobExecution);

        return $jobExecution;
    }

    private function dispatch(object $event): void
    {
        if ($this->eventDispatcher !== null) {
            $this->eventDispatcher->dispatch($event);
        }
    }
}
