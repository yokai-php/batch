<?php

declare(strict_types=1);

namespace Yokai\Batch\Exception;

use Throwable;

class RuntimeException extends \RuntimeException implements ExceptionInterface
{
    public function __construct(string $message = '', Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }

    public static function error(Throwable $error, string $message = null): self
    {
        return new static(
            \sprintf('%sAn error occurred.', $message ? \rtrim($message, '. ') . '. ' : ''),
            $error
        );
    }
}
