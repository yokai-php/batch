<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Test\Job\Item\Processor;

use PHPUnit\Framework\TestCase;
use Yokai\Batch\Job\Item\Processor\CallbackProcessor;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Test\Job\Item\Processor\TestDebugProcessor;

class TestDebugProcessorTest extends TestCase
{
    public function test(): void
    {
        $processor = new TestDebugProcessor(new CallbackProcessor(fn($item) => (string)$item));
        self::assertFalse($processor->wasInitialized());
        self::assertFalse($processor->wasProcessed());
        self::assertFalse($processor->wasFlushed());

        $processor->setJobExecution(JobExecution::createRoot('123456', 'testing'));
        $processor->initialize();
        self::assertTrue($processor->wasInitialized());

        self::assertSame('123', $processor->process(123));
        self::assertTrue($processor->wasProcessed());

        $processor->flush();
        self::assertTrue($processor->wasFlushed());

        $processor->initialize();
        self::assertTrue($processor->wasInitialized());
        self::assertFalse($processor->wasProcessed());
        self::assertTrue($processor->wasFlushed());
    }
}
