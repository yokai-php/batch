<?php

declare(strict_types=1);

namespace Yokai\Batch\Trigger\Scheduler;

use Yokai\Batch\JobExecution;

/**
 * A schedule is a component responsible to tell what jobs should be run at the moment.
 */
interface SchedulerInterface
{
    /**
     * Get list of job to schedule.
     *
     * @return ScheduledJob[]
     * @phpstan-return iterable<ScheduledJob>
     */
    public function get(JobExecution $execution): iterable;
}
