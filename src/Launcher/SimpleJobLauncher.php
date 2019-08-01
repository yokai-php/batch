<?php declare(strict_types=1);

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

        if (!$jobExecution->getStatus()->isExecutable()) {
            //todo this is not a normal state here, maybe it is a good idea to add a log or something
            return $jobExecution;
        }

        $this->dispatch(new PreExecuteEvent($jobExecution));

        $this->execute($job, $jobExecution);
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
