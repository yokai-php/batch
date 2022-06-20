<?php

declare(strict_types=1);

namespace Yokai\Batch;

use Throwable;

/**
 * This class represent an exception that occurred during a {@see JobExecution}.
 * Failure can be added to the execution via {@see JobExecution::addFailureException}.
 */
final class Failure implements \Stringable
{
    public function __construct(
        /**
         * The exception class
         */
        private string $class,
        /**
         * The exception message {@see Throwable::getMessage}
         */
        private string $message,
        /**
         * The exception code {@see Throwable::getCode}
         */
        private int $code,
        /**
         * Some extra parameters that a developer has provided
         * @phpstan-var array<string, string>
         */
        private array $parameters = [],
        /**
         * The exception trace {@see Throwable::getTraceAsString}
         */
        private ?string $trace = null,
    ) {
    }

    /**
     * Static constructor from an exception.
     *
     * @phpstan-param array<string, string> $parameters
     */
    public static function fromException(Throwable $exception, array $parameters = []): self
    {
        return new self(
            $exception::class,
            $exception->getMessage(),
            $exception->getCode(),
            $parameters,
            self::buildTrace($exception)
        );
    }

    public function __toString(): string
    {
        return \strtr($this->message, $this->parameters);
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * @phpstan-return array<string, string>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getTrace(): ?string
    {
        return $this->trace;
    }

    private static function buildTrace(Throwable $exception, bool $deep = false): string
    {
        $trace = ($deep ? 'Caused by: ' : '') . \get_class($exception) . ': ' . $exception->getMessage() .
            ' (at ' . $exception->getFile() . '(' . $exception->getLine() . '))' . PHP_EOL .
            \str_replace("\n", \PHP_EOL, $exception->getTraceAsString());

        if ($exception->getPrevious()) {
            $trace .= \PHP_EOL . self::buildTrace($exception->getPrevious(), true);
        }

        return $trace;
    }
}
