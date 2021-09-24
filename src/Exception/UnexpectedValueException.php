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

    /**
     * @param string      $expected
     * @param mixed       $argument
     * @param string|null $message
     *
     * @return self
     */
    public static function type(string $expected, $argument, string $message = null): self
    {
        $type = \is_object($argument) ? \get_class($argument) : \gettype($argument);

        return new self(
            $message,
            \sprintf(
                'Expecting argument to be %s, but got %s.',
                $expected,
                $type
            )
        );
    }

    /**
     * @param mixed[]     $expected
     * @param mixed       $argument
     * @param string|null $message
     *
     * @return self
     */
    public static function enum(array $expected, $argument, string $message = null): self
    {
        return new self(
            $message,
            \sprintf(
                'Expecting argument to be one of "%s", but got %s.',
                \implode('", "', $expected),
                $argument
            )
        );
    }

    /**
     * @param int|float|null $min      [PHP 8] Convert to union type
     * @param int|float|null $argument [PHP 8] Convert to union type
     * @param string|null    $message
     *
     * @return self
     */
    public static function min($min, $argument, string $message = null): self
    {
        return new self($message, \sprintf(
            'Expecting argument to be %s or more, got %s.',
            $min,
            $argument
        ));
    }

    /**
     * @param string      $expected
     * @param mixed       $argument
     * @param string|null $message
     *
     * @return self
     */
    public static function date(string $expected, $argument, string $message = null): self
    {
        return new self(
            $message,
            sprintf('Expecting a date with format "%s". Got "%s"', $expected, $argument)
        );
    }
}
