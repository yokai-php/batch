<?php

namespace Yokai\Batch\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Yokai\Batch\BatchStatus;

class BatchStatusTest extends TestCase
{
    /**
     * @dataProvider statuses
     */
    public function testStatus(int $value, string $label, bool $unsucessful)
    {
        $status = new BatchStatus($value);
        self::assertSame($label, (string)$status);
        self::assertSame($value, $status->getValue());
        self::assertTrue($status->is($value));
        self::assertTrue($status->isOneOf([$value]));
        self::assertSame($unsucessful, $status->isUnsuccessful());
    }

    public function statuses()
    {
        yield 'completed' => [BatchStatus::COMPLETED, 'COMPLETED', false];
        yield 'pending' => [BatchStatus::PENDING, 'PENDING', false];
        yield 'running' => [BatchStatus::RUNNING, 'RUNNING', false];
        yield 'stopped' => [BatchStatus::STOPPED, 'STOPPED', true];
        yield 'stopped' => [BatchStatus::ABANDONED, 'ABANDONED', true];
        yield 'failed' => [BatchStatus::FAILED, 'FAILED', true];
    }
}
