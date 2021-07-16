<?php

declare(strict_types=1);

namespace Yokai\Batch\Test\Launcher;

use Yokai\Batch\Factory\JobExecutionIdGeneratorInterface;
use Yokai\Batch\JobExecution;
use Yokai\Batch\JobParameters;
use Yokai\Batch\Launcher\JobLauncherInterface;

final class BufferingJobLauncher implements JobLauncherInterface
{
    private JobExecutionIdGeneratorInterface $idGenerator;

    /**
     * @var JobExecution[]
     */
    private array $executions = [];

    public function __construct(JobExecutionIdGeneratorInterface $idGenerator)
    {
        $this->idGenerator = $idGenerator;
    }

    /**
     * @inheritdoc
     */
    public function launch(string $name, array $configuration = []): JobExecution
    {
        $configuration['_id'] ??= $this->idGenerator->generate();
        $execution = JobExecution::createRoot($configuration['_id'], $name, null, new JobParameters($configuration));
        $this->executions[] = $execution;

        return $execution;
    }

    /**
     * @return JobExecution[]
     */
    public function getExecutions(): array
    {
        return $this->executions;
    }
}
