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
    /**
     * @var mixed
     */
    private $item;

    private ?SkipItemCauseInterface $cause;

    /**
     * @phpstan-var array<string, mixed>
     */
    private array $context;

    /**
     * @param mixed $item
     *
     * @phpstan-param array<string, mixed> $context
     */
    public function __construct($item, ?SkipItemCauseInterface $cause, array $context = [])
    {
        parent::__construct();
        $this->item = $item;
        $this->cause = $cause;
        $this->context = $context;
    }

    /**
     * @param mixed $item
     *
     * @phpstan-param array<string, mixed> $context
     */
    public static function justSkip($item, array $context = []): self
    {
        return new self($item, null, $context);
    }

    /**
     * @param mixed $item
     *
     * @phpstan-param array<string, mixed> $context
     */
    public static function onError($item, Throwable $error, array $context = []): self
    {
        return new self($item, new SkipItemOnError($error), $context);
    }

    /**
     * @param mixed $item
     *
     * @phpstan-param array<string, mixed> $context
     */
    public static function withWarning($item, string $message, array $context = []): self
    {
        return new self($item, new SkipItemWithWarning($message), $context);
    }

    /**
     * The item that has been skipped.
     * @return mixed
     */
    public function getItem()
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
