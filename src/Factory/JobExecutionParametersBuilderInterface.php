<?php

declare(strict_types=1);

namespace Yokai\Batch\Factory;

use Yokai\Batch\JobExecution;
use Yokai\Batch\JobParameters;

/**
 * Used by {@see JobExecutionFactory} to build default {@see JobExecution} parameters hash.
 */
interface JobExecutionParametersBuilderInterface
{
    /**
     * Returns an array hash to be given to {@see JobParameters}.
     *
     * @return array<string, mixed>
     */
    public function build(string $name): array;
}
