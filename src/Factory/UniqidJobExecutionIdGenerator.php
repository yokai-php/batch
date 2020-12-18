<?php

declare(strict_types=1);

namespace Yokai\Batch\Factory;

final class UniqidJobExecutionIdGenerator implements JobExecutionIdGeneratorInterface
{
    /**
     * @inheritdoc
     */
    public function generate(): string
    {
        return \uniqid();
    }
}
