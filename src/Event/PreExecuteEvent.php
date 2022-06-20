<?php

declare(strict_types=1);

namespace Yokai\Batch\Event;

use Yokai\Batch\Job\JobExecutor;

/**
 * This event is triggered by {@see JobExecutor}
 * whenever a job execution starts.
 */
final class PreExecuteEvent extends JobEvent
{
}
