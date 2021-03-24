<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Item;

use Throwable;

class InvalidItemException extends \RuntimeException
{
    /**
     * @var array
     */
    private array $parameters;

    public function __construct(string $message, array $parameters = [], int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->parameters = $parameters;
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
}
