<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Job\Item\Writer;

use Yokai\Batch\Job\Item\Writer\ConditionalWriter;
use PHPUnit\Framework\TestCase;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Test\Job\Item\Writer\InMemoryWriter;
use Yokai\Batch\Test\Job\Item\Writer\TestDebugWriter;

class ConditionalWriterTest extends TestCase
{
    public function testWriteSomething(): void
    {
        $writer = new ConditionalWriter(
            fn (int $number) => ($number % 2) === 0,
            $debugWriter = new TestDebugWriter($memoryWriter = new InMemoryWriter())
        );

        $writer->setJobExecution(JobExecution::createRoot('123', 'test.conditional_writer'));
        $writer->initialize();
        $writer->write([1, 2, 3, 4, 5]);
        $writer->write([6, 7, 8]);
        $writer->flush();

        $debugWriter->assertWasConfigured();
        $debugWriter->assertWasUsed();
        self::assertSame([[2, 4], [6, 8]], $memoryWriter->getBatchItems());
    }

    public function testWriteNothing(): void
    {
        $writer = new ConditionalWriter(
            fn () => false,
            $debugWriter = new TestDebugWriter($memoryWriter = new InMemoryWriter())
        );

        $writer->setJobExecution(JobExecution::createRoot('123', 'test.conditional_writer'));
        $writer->initialize();
        $writer->write([1, 2, 3, 4, 5]);
        $writer->write([6, 7, 8]);
        $writer->flush();

        $debugWriter->assertWasNotConfigured();
        $debugWriter->assertWasNotUsed();
        self::assertSame([], $memoryWriter->getBatchItems());
    }
}
