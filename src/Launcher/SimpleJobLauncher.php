<?php declare(strict_types=1);

namespace Yokai\Batch\Launcher;

use Throwable;
use Yokai\Batch\BatchStatus;
use Yokai\Batch\Exception\JobExecutionNotFoundException;
use Yokai\Batch\Factory\JobExecutionFactory;
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
     * @param JobRegistry                  $jobRegistry
     * @param JobExecutionFactory          $jobExecutionFactory
     * @param JobExecutionStorageInterface $jobExecutionStorage
     */
    public function __construct(
        JobRegistry $jobRegistry,
        JobExecutionFactory $jobExecutionFactory,
        JobExecutionStorageInterface $jobExecutionStorage
    ) {
        $this->jobRegistry = $jobRegistry;
        $this->jobExecutionFactory = $jobExecutionFactory;
        $this->jobExecutionStorage = $jobExecutionStorage;
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

        try {
            $job->execute($jobExecution);
        } catch (Throwable $exception) {
            $jobExecution->setStatus(BatchStatus::FAILED);
            $jobExecution->addFailureException($exception);
        }

        $this->jobExecutionStorage->store($jobExecution);

        return $jobExecution;
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
}
