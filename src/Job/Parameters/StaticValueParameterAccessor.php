<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Parameters;

use Yokai\Batch\JobExecution;

/**
 * This job parameter accessor implementation returns a static value from constructor.
 */
final class StaticValueParameterAccessor implements JobParameterAccessorInterface
{
    public function __construct(
        private mixed $value,
    ) {
    }

    /**
     * @inheritdoc
     */
    public function get(JobExecution $execution): mixed
    {
        return $this->value;
    }
}
