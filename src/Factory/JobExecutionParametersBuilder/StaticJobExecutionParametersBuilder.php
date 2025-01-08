<?php

declare(strict_types=1);

namespace Yokai\Batch\Factory\JobExecutionParametersBuilder;

use Yokai\Batch\Factory\JobExecutionParametersBuilderInterface;

/**
 * This {@see JobExecutionParametersBuilderInterface} has same default values for all jobs provided at construction.
 */
final class StaticJobExecutionParametersBuilder implements JobExecutionParametersBuilderInterface
{
    public function __construct(
        /**
         * @var array<string, mixed>
         */
        private readonly array $parameters,
    ) {
    }

    public function build(string $name): array
    {
        return $this->parameters;
    }
}
