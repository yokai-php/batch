<?php

namespace Yokai\Batch\Bridge\Symfony\Messenger;

use Symfony\Component\Messenger\MessageBusInterface;
use Yokai\Batch\BatchStatus;
use Yokai\Batch\Factory\JobExecutionFactory;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Launcher\JobLauncherInterface;
use Yokai\Batch\Storage\JobExecutionStorageInterface;

final class DispatchMessageJobLauncher implements JobLauncherInterface
{
    /**
     * @var JobExecutionFactory
     */
    private $jobExecutionFactory;

    /**
     * @var JobExecutionStorageInterface
     */
    private $jobExecutionStorage;

    /**
     * @var MessageBusInterface
     */
    private $messageBus;

    public function __construct(
        JobExecutionFactory $jobExecutionFactory,
        JobExecutionStorageInterface $jobExecutionStorage,
        MessageBusInterface $messageBus
    ) {
        $this->jobExecutionFactory = $jobExecutionFactory;
        $this->messageBus = $messageBus;
        $this->jobExecutionStorage = $jobExecutionStorage;
    }

    public function launch(string $name, array $configuration = []): JobExecution
    {
        $configuration['_id'] = $configuration['_id'] ?? uniqid();

        // create and store execution before dispatching message
        // guarantee job execution exists if message bus transport is asynchronous
        $jobExecution = $this->jobExecutionFactory->create($name, $configuration);
        $jobExecution->setStatus(BatchStatus::PENDING);
        $this->jobExecutionStorage->store($jobExecution);

        // dispatch message
        $this->messageBus->dispatch(new LaunchJobMessage($name, $configuration));

        // re-fetch job execution from storage
        // if transport is synchronous, job execution may have been filled during execution
        $jobExecution = $this->jobExecutionStorage->retrieve($jobExecution->getJobName(), $jobExecution->getId());

        return $jobExecution;
    }
}
