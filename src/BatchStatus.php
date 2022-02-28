<?php

declare(strict_types=1);

namespace Yokai\Batch;

use Yokai\Batch\Job\JobWithChildJobs;
use Yokai\Batch\Launcher\JobLauncherInterface;

/**
 * The status of a job execution.
 */
final class BatchStatus implements \Stringable
{
    /**
     * The job execution has not started yet.
     * This is usually because you are using an asynchronous {@see JobLauncherInterface}.
     */
    public const PENDING = 1;

    /**
     * The job execution has started and is not finished.
     */
    public const RUNNING = 2;

    /**
     * Something has stopped the job execution.
     * (not used yet)
     */
    public const STOPPED = 3;

    /**
     * The job execution has finished without error.
     */
    public const COMPLETED = 4;

    /**
     * The job execution was not and won't be executed.
     * This is usually because you had a {@see JobWithChildJobs}
     * and one of your siblings job execution has failed.
     */
    public const ABANDONED = 5;

    /**
     * An error occurred during the job execution.
     * Try finding more information in {@see JobExecution::$failures}.
     */
    public const FAILED = 6;

    private const LABELS = [
        self::PENDING => 'PENDING',
        self::RUNNING => 'RUNNING',
        self::STOPPED => 'STOPPED',
        self::COMPLETED => 'COMPLETED',
        self::ABANDONED => 'ABANDONED',
        self::FAILED => 'FAILED',
    ];

    public function __construct(
        private int $value,
    ) {
    }

    /**
     * The status label.
     */
    public function __toString(): string
    {
        return self::LABELS[$this->value] ?? 'UNKNOWN';
    }

    public function getValue(): int
    {
        return $this->value;
    }

    /**
     * Compare status value to another.
     */
    public function is(int $value): bool
    {
        return $this->value === $value;
    }

    /**
     * Compare status value to some others.
     *
     * @param int[] $values
     */
    public function isOneOf(array $values): bool
    {
        return in_array($this->value, $values, true);
    }

    /**
     * Executed and not succeed.
     */
    public function isUnsuccessful(): bool
    {
        return $this->isOneOf([self::ABANDONED, self::STOPPED, self::FAILED]);
    }

    /**
     * Executed and succeed.
     */
    public function isSuccessful(): bool
    {
        return $this->is(self::COMPLETED);
    }

    /**
     * Not executed yet.
     */
    public function isExecutable(): bool
    {
        return $this->is(self::PENDING);
    }
}
