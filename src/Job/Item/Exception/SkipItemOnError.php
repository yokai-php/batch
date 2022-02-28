<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Item\Exception;

use Throwable;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Warning;

/**
 * Skip item when an exception occurs and leave a warning with exception to the {@see JobExecution}.
 */
final class SkipItemOnError implements SkipItemCauseInterface
{
    public function __construct(
        private Throwable $error,
    ) {
    }

    /**
     * @inheritdoc
     */
    public function report(JobExecution $execution, int|string $index, mixed $item): void
    {
        $execution->getSummary()->increment('errored');
        $execution->addWarning(
            new Warning(
                'An error occurred.',
                [],
                [
                    'itemIndex' => $index,
                    'item' => $item,
                    'class' => $this->error::class,
                    'message' => $this->error->getMessage(),
                    'code' => $this->error->getCode(),
                    'trace' => $this->error->getTraceAsString(),
                ]
            )
        );
    }

    public function getError(): Throwable
    {
        return $this->error;
    }
}
