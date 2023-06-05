<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Item\Exception;

use Yokai\Batch\JobExecution;
use Yokai\Batch\Warning;

/**
 * Skip item and leave an arbitrary warning to the {@see JobExecution}.
 */
final class SkipItemWithWarning implements SkipItemCauseInterface
{
    public function __construct(
        private string $message,
    ) {
    }

    public function report(JobExecution $execution, int|string $index, mixed $item): void
    {
        $execution->addWarning(new Warning($this->message, [], ['itemIndex' => $index, 'item' => $item]));
    }
}
