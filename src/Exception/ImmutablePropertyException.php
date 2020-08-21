<?php

declare(strict_types=1);

namespace Yokai\Batch\Exception;

use Throwable;

class ImmutablePropertyException extends \LogicException
{
    public function __construct(string $class, string $property, int $code = 0, Throwable $previous = null)
    {
        parent::__construct(
            sprintf('%s:%s property is immutable and therefor cannot be modified.', $class, $property),
            $code,
            $previous
        );
    }
}
