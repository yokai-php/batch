<?php

declare(strict_types=1);

namespace Yokai\Batch\Exception;

class UndefinedJobParameterException extends InvalidArgumentException
{
    public function __construct(string $name)
    {
        parent::__construct(sprintf('Parameter "%s" is undefined', $name));
    }
}
