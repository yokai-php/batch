<?php

declare(strict_types=1);

namespace Yokai\Batch;

use Throwable;

final class Failure
{
    /**
     * @var string
     */
    private string $class;

    /**
     * @var string
     */
    private string $message;

    /**
     * @var int
     */
    private int $code;

    /**
     * @phpstan-var array<string, string>
     */
    private array $parameters;

    /**
     * @var string|null
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

    /**
     * @return string
     */
    public function __toString(): string
    {
        return strtr($this->message, $this->parameters);
    }

    /**
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return int
     */
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

    /**
     * @return string|null
     */
    public function getTrace(): ?string
    {
        return $this->trace;
    }
}
