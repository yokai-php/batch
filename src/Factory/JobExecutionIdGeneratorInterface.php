<?php

declare(strict_types=1);

namespace Yokai\Batch\Factory;

interface JobExecutionIdGeneratorInterface
{
    /**
     * @return string
     */
    public function generate(): string;
}
