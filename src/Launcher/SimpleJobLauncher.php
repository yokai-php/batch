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

class SimpleJobLauncher implements JobLauncherInterface
{
    /**
     * @var JobRegistry
     */
    private $jobRegistry;

    /**
     * @var JobExecutionFactory
     */
    private $jobExecutionFactory;

    /**
     * @var JobExecutionStorageInterface
     */
    private $jobExecutionStorage;

    /**
     * @var EventDispatcherInterface|null
     */
    private $eventDispatcher;

    /**
     * @param JobRegistry                   $jobRegistry
     * @param JobExecutionFactory           $jobExecutionFactory
     * @param JobExecutionStorageInterface  $jobExecutionStorage
     * @param EventDispatcherInterface|null $eventDispatcher
     */
    public function __construct(
        JobRegistry $jobRegistry,
        JobExecutionFactory $jobExecutionFactory,
        JobExecutionStorageInterface $jobExecutionStorage,
        ?EventDispatcherInterface $eventDispatcher
    ) {
        $this->jobRegistry = $jobRegistry;
        $this->jobExecutionFactory = $jobExecutionFactory;
        $this->jobExecutionStorage = $jobExecutionStorage;
        $this->eventDispatcher = $eventDispatcher;
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

        $this->store($jobExecution);

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

    private function store(JobExecution $execution): void
    {
        $this->jobExecutionStorage->store($execution);
    }

    private function getJobExecution(string $name, array $configuration): JobExecution
    {
        $id = $configuration['_id'] ?? null;
        if (is_string($id)) {
            try {
                return $this->jobExecutionStorage->retrieve($name, $id);
            } catch (JobExecutionNotFoundException $notFound) {
            }
        }

        return $this->jobExecutionFactory->create($name, $configuration);
    }

    private function dispatch(object $event): void
    {
        if ($this->eventDispatcher !== null) {
            $this->eventDispatcher->dispatch($event);
        }
    }
}
