<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Item;

use Throwable;

class InvalidItemException extends \RuntimeException
{
    /**
     * @phpstan-var array<string, mixed>
     */
    private array $parameters;

    /**
     * @phpstan-param array<string, mixed> $parameters
     */
    public function __construct(string $message, array $parameters = [], int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->parameters = $parameters;
    }

    /**
     * @phpstan-return array<string, mixed>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
}
