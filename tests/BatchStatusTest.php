<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Yokai\Batch\BatchStatus;

final class BatchStatusTest extends TestCase
{
    #[DataProvider('statuses')]
    public function testStatus(BatchStatus $status, string $label, bool $unsuccessful): void
    {
        self::assertSame($label, $status->name);
        self::assertSame($unsuccessful, $status->isUnsuccessful());
    }

    public static function statuses(): \Generator
    {
        yield 'completed' => [BatchStatus::Completed, 'Completed', false];
        yield 'pending' => [BatchStatus::Pending, 'Pending', false];
        yield 'running' => [BatchStatus::Running, 'Running', false];
        yield 'stopped' => [BatchStatus::Stopped, 'Stopped', true];
        yield 'abandoned' => [BatchStatus::Abandoned, 'Abandoned', true];
        yield 'failed' => [BatchStatus::Failed, 'Failed', true];
    }
}
