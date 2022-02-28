<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Item\Exception;

use Throwable;
use Yokai\Batch\Exception\RuntimeException;
use Yokai\Batch\Job\Item\ItemJob;
use Yokai\Batch\Job\Item\ItemProcessorInterface;
use Yokai\Batch\JobExecution;

/**
 * This exception might be thrown by any {@see ItemProcessorInterface}.
 * If such thing happen, the item will be skipped and thus never written.
 */
final class SkipItemException extends RuntimeException
{
    public function __construct(
        private mixed $item,
        private ?SkipItemCauseInterface $cause,
        /**
         * @phpstan-var array<string, mixed>
         */
        private array $context = [],
    ) {
        parent::__construct();
    }

    /**
     * Use this method when it's normal to skip this item.
     *
     * @phpstan-param array<string, mixed> $context
     */
    public static function justSkip(mixed $item, array $context = []): self
    {
        return new self($item, null, $context);
    }

    /**
     * Use this method when an error occurs, and you want to mark this item as errored.
     *
     * @phpstan-param array<string, mixed> $context
     */
    public static function onError(mixed $item, Throwable $error, array $context = []): self
    {
        return new self($item, new SkipItemOnError($error), $context);
    }

    /**
     * Use this method when something seems weird with an item, and you want to warn about it.
     *
     * @phpstan-param array<string, mixed> $context
     */
    public static function withWarning(mixed $item, string $message, array $context = []): self
    {
        return new self($item, new SkipItemWithWarning($message), $context);
    }

    /**
     * The item that has been skipped.
     */
    public function getItem(): mixed
    {
        return $this->item;
    }

    /**
     * The cause of the exception.
     * Will be used by {@see ItemJob} to leave a trace in {@see JobExecution}.
     */
    public function getCause(): ?SkipItemCauseInterface
    {
        return $this->cause;
    }

    /**
     * Some contextual information provided by the developer.
     * @phpstan-return array<string, mixed>
     */
    public function getContext(): array
    {
        return $this->context;
    }
}
