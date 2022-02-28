<?php

declare(strict_types=1);

namespace Yokai\Batch\Exception;

class UndefinedJobException extends InvalidArgumentException
{
    public function __construct(string $name)
    {
        parent::__construct(sprintf('Job "%s" is undefined', $name));
    }
}
