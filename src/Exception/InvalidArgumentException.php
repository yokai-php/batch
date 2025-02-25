<?php

declare(strict_types=1);

namespace Yokai\Batch\Exception;

use Throwable;

class InvalidArgumentException extends \InvalidArgumentException implements ExceptionInterface
{
    public function __construct(string $message = '', Throwable|null $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
