<?php

declare(strict_types=1);

namespace Yokai\Batch\Factory;

/**
 * This {@see JobExecutionIdGeneratorInterface} will use
 * php {@see uniqid} function to generate job ids.
 */
final class UniqidJobExecutionIdGenerator implements JobExecutionIdGeneratorInterface
{
    public function generate(): string
    {
        return \uniqid();
    }
}
