<?php

declare(strict_types=1);

namespace Yokai\Batch\Factory;

use Yokai\Batch\JobExecution;
use Yokai\Batch\JobParameters;

final class JobExecutionFactory
{
    /**
     * @var JobExecutionIdGeneratorInterface
     */
    private JobExecutionIdGeneratorInterface $idGenerator;

    /**
     * @param JobExecutionIdGeneratorInterface $idGenerator
     */
    public function __construct(JobExecutionIdGeneratorInterface $idGenerator)
    {
        $this->idGenerator = $idGenerator;
    }

    /**
     * @param string $name
     * @param array  $configuration
     *
     * @return JobExecution
     */
    public function create(string $name, array $configuration = []): JobExecution
    {
        $configuration['_id'] ??= $this->idGenerator->generate();

        return JobExecution::createRoot($configuration['_id'], $name, null, new JobParameters($configuration));
    }
}
