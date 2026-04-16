<?php

declare(strict_types=1);

namespace Yokai\Batch\Factory;

use DateTimeImmutable;
use Yokai\Batch\JobExecution;
use Yokai\Batch\JobParameters;

/**
 * Create a {@see JobExecution} from scalar members.
 */
final readonly class JobExecutionFactory
{
    public function __construct(
        private JobExecutionIdGeneratorInterface $idGenerator,
        private JobExecutionParametersBuilderInterface $parametersBuilder,
        private JobExecutionLoggerFactoryInterface $loggerFactory,
    ) {
    }

    /**
     * Create a {@see JobExecution}.
     *
     * @param array<string, mixed> $configuration
     */
    public function create(string $name, array $configuration = []): JobExecution
    {
        $configuration += $this->parametersBuilder->build($name);
        /** @var string $id */
        $id = $configuration['_id'] ??= $this->idGenerator->generate();

        $jobExecution = JobExecution::createRoot(
            id: $id,
            jobName: $name,
            parameters: new JobParameters($configuration),
            logger: $this->loggerFactory->create(),
        );
        $jobExecution->setLaunchedAt(new DateTimeImmutable());

        return $jobExecution;
    }
}
