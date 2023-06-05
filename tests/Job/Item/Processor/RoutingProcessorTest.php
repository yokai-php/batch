<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Job\Item\Processor;

use PHPUnit\Framework\TestCase;
use Yokai\Batch\Exception\UnexpectedValueException;
use Yokai\Batch\Finder\CallbackFinder;
use Yokai\Batch\Job\Item\Processor\CallbackProcessor;
use Yokai\Batch\Job\Item\Processor\NullProcessor;
use Yokai\Batch\Job\Item\Processor\RoutingProcessor;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Test\Finder\DummyFinder;
use Yokai\Batch\Test\Job\Item\Processor\TestDebugProcessor;

class RoutingProcessorTest extends TestCase
{
    public function test(): void
    {
        $upperProcessor = new TestDebugProcessor(new CallbackProcessor(fn(string $item) => \mb_strtoupper($item)));
        $multiplyProcessor = new TestDebugProcessor(new CallbackProcessor(fn(float $item) => (int)\round($item * 100)));
        $notCalledProcessor = new TestDebugProcessor(new NullProcessor());
        $defaultProcessor = new TestDebugProcessor(new NullProcessor());
        $processor = new RoutingProcessor(new CallbackFinder([
            [fn($item) => \is_string($item), $upperProcessor],
            [fn($item) => \is_float($item), $multiplyProcessor],
            [fn($item) => false, $notCalledProcessor],
        ], $defaultProcessor));

        $jobExecution = JobExecution::createRoot('123456', 'testing');

        $processor->setJobExecution($jobExecution);
        $processor->initialize();
        self::assertSame('JOHN', $processor->process('John'));
        self::assertSame(123, $processor->process(123));
        self::assertSame(1024, $processor->process(10.237));
        $processor->flush();

        $upperProcessor->assertWasConfigured();
        $upperProcessor->assertWasUsed();
        $multiplyProcessor->assertWasConfigured();
        $multiplyProcessor->assertWasUsed();
        $notCalledProcessor->assertWasNotConfigured();
        $notCalledProcessor->assertWasNotUsed();
        $defaultProcessor->assertWasConfigured();
        $defaultProcessor->assertWasUsed();
    }

    /**
     * Finder must return ItemProcessorInterface, otherwise an exception will be thrown.
     */
    public function testMisconfiguredFinder(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $processor = new RoutingProcessor(new DummyFinder(new \stdClass()));
        $processor->process('anything');
    }
}
