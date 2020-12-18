<?php

declare(strict_types=1);

namespace Yokai\Batch\Exception;

use Throwable;
use Yokai\Batch\Job\Item\InitializableInterface;

class BadMethodCallException extends \BadMethodCallException implements ExceptionInterface
{
    public function __construct(string $message = '', Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }

    public static function itemComponentNotInitialized(InitializableInterface $component): self
    {
        return new self(
            \sprintf(
                '%s component should have been initialized. Call %s::initialize().',
                \get_class($component),
                \get_class($component)
            )
        );
    }
}
