<?php

declare(strict_types=1);

namespace Yokai\Batch\Exception;

use Throwable;

class UndefinedJobException extends InvalidArgumentException
{
    public function __construct(string $name, Throwable $previous = null)
    {
        parent::__construct(sprintf('Job "%s" is undefined', $name), $previous);
    }
}
