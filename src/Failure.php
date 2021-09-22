<?php

declare(strict_types=1);

namespace Yokai\Batch;

use Throwable;

/**
 * This class represent an exception that occurred during a {@see JobExecution}.
 * Failure can be added to the execution via {@see JobExecution::addFailureException}.
 */
final class Failure
{
    /**
     * The exception class
     */
    private string $class;

    /**
     * The exception message {@see Throwable::getMessage}
     */
    private string $message;

    /**
     * The exception code {@see Throwable::getCode}
     */
    private int $code;

    /**
     * Some extra parameters that a developer has provided
     * @phpstan-var array<string, string>
     */
    private array $parameters;

    /**
     * The exception trace {@see Throwable::getTraceAsString}
     */
    private ?string $trace;

    /**
     * @phpstan-param array<string, string> $parameters
     */
    public function __construct(
        string $class,
        string $message,
        int $code,
        array $parameters = [],
        string $trace = null
    ) {
        $this->class = $class;
        $this->message = $message;
        $this->code = $code;
        $this->parameters = $parameters;
        $this->trace = $trace;
    }

    /**
     * @phpstan-param array<string, string> $parameters
     */
    public static function fromException(Throwable $exception, array $parameters = []): self
    {
        return new self(
            get_class($exception),
            $exception->getMessage(),
            $exception->getCode(),
            $parameters,
            $exception->getTraceAsString()
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
}
