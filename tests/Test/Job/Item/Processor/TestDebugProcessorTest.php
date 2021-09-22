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
        $processor->assertWasNotConfigured();
        $processor->assertWasNotUsed();

        $processor->configure(JobExecution::createRoot('123456', 'testing'));
        $processor->initialize();
        self::assertSame('123', $processor->process(123));
        $processor->flush();

        $processor->assertWasConfigured();
        $processor->assertWasUsed();
    }
}
