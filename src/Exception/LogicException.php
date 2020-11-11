<?php

declare(strict_types=1);

namespace Yokai\Batch\Exception;

use Throwable;

class LogicException extends \LogicException implements ExceptionInterface
{
    public function __construct(string $message = '', Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
