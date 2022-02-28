<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Parameters;

use Yokai\Batch\Exception\CannotAccessParameterException;
use Yokai\Batch\JobExecution;

/**
 * This job parameter accessor implementation return a static value
 * if the decorated job parameter accessor fails accessing parameter.
 */
final class DefaultParameterAccessor implements JobParameterAccessorInterface
{
    public function __construct(
        private JobParameterAccessorInterface $accessor,
        private mixed $default,
    ) {
    }

    /**
     * @inheritdoc
     */
    public function get(JobExecution $execution): mixed
    {
        try {
            return $this->accessor->get($execution);
        } catch (CannotAccessParameterException) {
            return $this->default;
        }
    }
}
