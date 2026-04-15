<?php

declare(strict_types=1);

namespace Yokai\Batch\Factory;

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

        return JobExecution::createRoot($id, $name, null, new JobParameters($configuration));
    }
}
