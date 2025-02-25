<?php

declare(strict_types=1);

namespace Yokai\Batch\Exception;

use Throwable;

class RuntimeException extends \RuntimeException implements ExceptionInterface
{
    public function __construct(string $message = '', Throwable|null $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }

    public static function error(Throwable $error, string|null $message = null): self
    {
        return new self(\sprintf('%sAn error occurred.', $message ? \rtrim($message, '. ') . '. ' : ''), $error);
    }
}
