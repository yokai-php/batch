<?php

declare(strict_types=1);

namespace Yokai\Batch\Event;

use Yokai\Batch\Job\Item\ItemJob;
use Yokai\Batch\Job\Item\ItemWriterInterface;
use Yokai\Batch\Job\Item\Writer\DispatchEventsWriter;

/**
 * This event is triggered by {@see DispatchEventsWriter}
 * whenever an {@see ItemJob} is calling the {@see ItemWriterInterface} to write
 * before actual write is performed.
 */
final class PreWriteEvent extends JobEvent
{
}
