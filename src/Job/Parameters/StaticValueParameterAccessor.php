<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Parameters;

use Yokai\Batch\JobExecution;

/**
 * This job parameter accessor implementation returns a static value from constructor.
 */
final class StaticValueParameterAccessor implements JobParameterAccessorInterface
{
    /**
     * @var mixed
     */
    private $value;

    /**
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @inheritdoc
     */
    public function get(JobExecution $execution)
    {
        return $this->value;
    }
}
