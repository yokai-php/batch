<?php

declare(strict_types=1);

namespace Yokai\Batch\Factory\JobExecutionParametersBuilder;

use Yokai\Batch\Factory\JobExecutionParametersBuilderInterface;

/**
 * This {@see JobExecutionParametersBuilderInterface} has different default values per job provided at construction.
 */
final readonly class PerJobJobExecutionParametersBuilder implements JobExecutionParametersBuilderInterface
{
    public function __construct(
        /**
         * @var array<string, array<string, mixed>>
         */
        private array $perJobParameters,
    ) {
    }

    public function build(string $name): array
    {
        return $this->perJobParameters[$name] ?? [];
    }
}
