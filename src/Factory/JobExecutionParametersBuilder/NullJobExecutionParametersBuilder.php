<?php

declare(strict_types=1);

namespace Yokai\Batch\Factory\JobExecutionParametersBuilder;

use Yokai\Batch\Factory\JobExecutionParametersBuilderInterface;

/**
 * This {@see JobExecutionParametersBuilderInterface} has no default values to add.
 */
final class NullJobExecutionParametersBuilder implements JobExecutionParametersBuilderInterface
{
    public function build(string $name): array
    {
        return [];
    }
}
