<?php

declare(strict_types=1);

namespace Yokai\Batch;

/**
 * This class represent something weird that should be reported but that will not block execution.
 * It is usually something about sanity/validation.
 * Warning can be added to the execution via {@see JobExecution::addWarning}.
 */
final class Warning
{
    /**
     * The warning message.
     */
    private string $message;

    /**
     * The warning message parameters.
     * @phpstan-var array<string, string>
     */
    private array $parameters;

    /**
     * Some extra parameters that a developer has provided.
     * @phpstan-var array<string, mixed>
     */
    private array $context;

    /**
     * @phpstan-param array<string, string> $parameters
     * @phpstan-param array<string, mixed> $context
     */
    public function __construct(string $message, array $parameters = [], array $context = [])
    {
        $this->message = $message;
        $this->parameters = $parameters;
        $this->context = $context;
    }

    public function __toString(): string
    {
        return strtr($this->message, $this->parameters);
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @phpstan-return array<string, mixed>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @phpstan-return array<string, mixed>
     */
    public function getContext(): array
    {
        return $this->context;
    }
}
