<?php

declare(strict_types=1);

namespace Yokai\Batch\Factory;

use Yokai\Batch\JobExecution;
use Yokai\Batch\JobParameters;

/**
 * Create a {@see JobExecution} from scalar members.
 */
final class JobExecutionFactory
{
    public function __construct(
        private JobExecutionIdGeneratorInterface $idGenerator,
    ) {
    }

    /**
     * Create a {@see JobExecution}.
     *
     * @phpstan-param array<string, mixed> $configuration
     */
    public function create(string $name, array $configuration = []): JobExecution
    {
        /** @var string $id */
        $id = $configuration['_id'] ??= $this->idGenerator->generate();

        return JobExecution::createRoot($id, $name, null, new JobParameters($configuration));
    }
}
