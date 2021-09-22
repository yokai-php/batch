<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Job\Item\Processor;

use PHPUnit\Framework\TestCase;
use Yokai\Batch\Job\Item\Processor\CallbackProcessor;
use Yokai\Batch\Job\Item\Processor\ChainProcessor;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Test\Job\Item\Processor\TestDebugProcessor;

class ChainProcessorTest extends TestCase
{
    public function testProcess(): void
    {
        $chain = [
            // substract 1
            new TestDebugProcessor(
                new CallbackProcessor(fn(int $number) => $number - 1)
            ),
            // multiply by 2
            new TestDebugProcessor(
                new CallbackProcessor(fn(int $number) => $number * 2)
            ),
            // add 10
            new TestDebugProcessor(
                new CallbackProcessor(fn(int $number) => $number + 10)
            ),
        ];

        $processor = new ChainProcessor($chain);

        $processor->setJobExecution(JobExecution::createRoot('123456789', 'export'));
        $processor->initialize();
        // formula is (X - 1) * 2 + 10
        self::assertSame(14, $processor->process(3));
        self::assertSame(28, $processor->process(10));
        $processor->flush();

        foreach ($chain as $decorated) {
            $decorated->assertWasConfigured();
            $decorated->assertWasUsed();
        }
    }
}
