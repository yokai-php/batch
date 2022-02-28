<?php

declare(strict_types=1);

namespace Yokai\Batch\Factory;

use Yokai\Batch\JobExecution;

/**
 * Generate {@see JobExecution::$id} values when none provided at job startup.
 */
interface JobExecutionIdGeneratorInterface
{
    public function generate(): string;
}
