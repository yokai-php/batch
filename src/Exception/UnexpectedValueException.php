<?php

declare(strict_types=1);

namespace Yokai\Batch\Exception;

use Throwable;

class UnexpectedValueException extends \UnexpectedValueException implements ExceptionInterface
{
    public function __construct(string $base = null, string $message = '', Throwable $previous = null)
    {
        parent::__construct(($base ? \rtrim($base, '. ') . '. ' : '') . $message, 0, $previous);
    }

    public static function type(string $expected, mixed $argument, string $message = null): self
    {
        return new self(
            $message,
            \sprintf('Expecting argument to be %s, but got %s.', $expected, \get_debug_type($argument))
        );
    }

    /**
     * @param mixed[] $expected
     */
    public static function enum(array $expected, mixed $argument, string $message = null): self
    {
        return new self(
            $message,
            \sprintf(
                'Expecting argument to be one of "%s", but got %s.',
                \implode('", "', $expected),
                \is_scalar($argument) ? $argument : \get_debug_type($argument)
            )
        );
    }

    public static function min(float|int|null $min, float|int|null $argument, string $message = null): self
    {
        return new self($message, \sprintf('Expecting argument to be %s or more, got %s.', $min, $argument));
    }

    public static function date(string $expected, mixed $argument, string $message = null): self
    {
        return new self(
            $message,
            sprintf(
                'Expecting a date with format "%s". Got "%s"',
                $expected,
                \is_scalar($argument) ? $argument : \get_debug_type($argument)
            )
        );
    }
}
