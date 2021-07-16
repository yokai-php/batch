<?php

declare(strict_types=1);

namespace Yokai\Batch;

final class Warning
{
    /**
     * @var string
     */
    private string $message;

    /**
     * @var array
     * @phpstan-var array<string, string>
     */
    private array $parameters;

    /**
     * @var array
     * @phpstan-var array<string, string>
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
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return array
     * @phpstan-return array<string, string>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @return array
     * @phpstan-return array<string, string>
     */
    public function getContext(): array
    {
        return $this->context;
    }
}
