<?php

declare(strict_types=1);

namespace Yokai\Batch\Event;

/**
 * This event is triggered by {@see JobExecutor}
 * whenever a job execution fails or succeed.
 */
final class PostExecuteEvent extends JobEvent
{
}
