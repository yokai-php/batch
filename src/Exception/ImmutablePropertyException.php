<?php

declare(strict_types=1);

namespace Yokai\Batch\Exception;

final class ImmutablePropertyException extends LogicException
{
    public function __construct(string $class, string $property)
    {
        parent::__construct(
            \sprintf('%s:%s property is immutable and therefor cannot be modified.', $class, $property),
        );
    }
}
