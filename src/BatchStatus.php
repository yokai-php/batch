<?php

declare(strict_types=1);

namespace Yokai\Batch;

use Yokai\Batch\Job\JobWithChildJobs;
use Yokai\Batch\Launcher\JobLauncherInterface;

/**
 * The status of a job execution.
 */
enum BatchStatus: int
{
    /**
     * The job execution has not started yet.
     * This is usually because you are using an asynchronous {@see JobLauncherInterface}.
     */
    case Pending = 1;

    /**
     * The job execution has started and is not finished.
     */
    case Running = 2;

    /**
     * Something has stopped the job execution.
     * (not used yet)
     */
    case Stopped = 3;

    /**
     * The job execution has finished without error.
     */
    case Completed = 4;

    /**
     * The job execution was not and won't be executed.
     * This is usually because you had a {@see JobWithChildJobs}
     * and one of your siblings job execution has failed.
     */
    case Abandoned = 5;

    /**
     * An error occurred during the job execution.
     * Try finding more information in {@see JobExecution::$failures}.
     */
    case Failed = 6;

    /**
     * Executed and not succeed.
     */
    public function isUnsuccessful(): bool
    {
        return \in_array($this, [self::Abandoned, self::Stopped, self::Failed], true);
    }

    /**
     * Executed and succeed.
     */
    public function isSuccessful(): bool
    {
        return $this === self::Completed;
    }

    /**
     * Not executed yet.
     */
    public function isExecutable(): bool
    {
        return $this === self::Pending;
    }
}
