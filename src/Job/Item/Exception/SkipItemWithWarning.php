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
    private string $message;

    public function __construct(string $message)
    {
        $this->message = $message;
    }

    /**
     * @inheritdoc
     */
    public function report(JobExecution $execution, $index, $item): void
    {
        $execution->addWarning(new Warning($this->message, [], ['itemIndex' => $index, 'item' => $item]));
    }
}
