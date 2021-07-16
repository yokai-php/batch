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
    private JobParameterAccessorInterface $accessor;

    /**
     * @var mixed
     */
    private $default;

    /**
     * @param mixed $default
     */
    public function __construct(JobParameterAccessorInterface $accessor, $default)
    {
        $this->accessor = $accessor;
        $this->default = $default;
    }

    /**
     * @inheritdoc
     */
    public function get(JobExecution $execution)
    {
        try {
            return $this->accessor->get($execution);
        } catch (CannotAccessParameterException $exception) {
            return $this->default;
        }
    }
}
