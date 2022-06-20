<?php

declare(strict_types=1);

namespace Yokai\Batch\Test\Launcher;

use Yokai\Batch\Factory\JobExecutionIdGeneratorInterface;
use Yokai\Batch\JobExecution;
use Yokai\Batch\JobParameters;
use Yokai\Batch\Launcher\JobLauncherInterface;

/**
 * This {@see JobLauncherInterface} should be used in test.
 * It will remember launched executions
 * and will allow you to fetch these for assertions.
 */
final class BufferingJobLauncher implements JobLauncherInterface
{
    /**
     * @var JobExecution[]
     */
    private array $executions = [];

    public function __construct(
        private JobExecutionIdGeneratorInterface $idGenerator,
    ) {
    }

    /**
     * @inheritdoc
     */
    public function launch(string $name, array $configuration = []): JobExecution
    {
        /** @var string $id */
        $id = $configuration['_id'] ??= $this->idGenerator->generate();
        $execution = JobExecution::createRoot($id, $name, null, new JobParameters($configuration));
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
