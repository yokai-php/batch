<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Job\Item\Writer;

use PHPUnit\Framework\TestCase;
use Yokai\Batch\Exception\UnexpectedValueException;
use Yokai\Batch\Job\Item\Exception\SkipItemException;
use Yokai\Batch\Job\Item\Processor\CallbackProcessor;
use Yokai\Batch\Job\Item\Processor\NullProcessor;
use Yokai\Batch\Job\Item\Writer\TransformingWriter;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Test\Job\Item\Processor\TestDebugProcessor;
use Yokai\Batch\Test\Job\Item\Writer\InMemoryWriter;
use Yokai\Batch\Test\Job\Item\Writer\TestDebugWriter;

class TransformingWriterTest extends TestCase
{
    public function test(): void
    {
        $writer = new TransformingWriter(
            $debugProcessor = new TestDebugProcessor(new CallbackProcessor(fn ($string) => \strtoupper($string))),
            $debugWriter = new TestDebugWriter($innerWriter = new InMemoryWriter())
        );

        $writer->setJobExecution(JobExecution::createRoot('123', 'test.transforming_writer'));
        $writer->initialize();
        $writer->write(['one', 'two', 'three']);
        $writer->flush();

        $debugProcessor->assertWasConfigured();
        $debugProcessor->assertWasUsed();
        $debugWriter->assertWasConfigured();
        $debugWriter->assertWasUsed();
        self::assertSame(['ONE', 'TWO', 'THREE'], $innerWriter->getItems());
    }

    public function testSkipItems(): void
    {
        $writer = new TransformingWriter(
            $debugProcessor = new TestDebugProcessor(
                new CallbackProcessor(
                    fn ($item) => throw SkipItemException::withWarning($item, 'Skipped for test purpose')
                )
            ),
            $debugWriter = new TestDebugWriter($innerWriter = new InMemoryWriter())
        );

        $writer->setJobExecution($execution = JobExecution::createRoot('123', 'test.transforming_writer'));
        $writer->initialize();
        $writer->write(['one', 'two', 'three']);
        $writer->flush();

        $debugProcessor->assertWasConfigured();
        $debugProcessor->assertWasUsed();
        $debugWriter->assertWasConfigured();
        $debugWriter->assertWasNotUsed(true, true);
        self::assertSame([], $innerWriter->getItems());
        self::assertCount(3, $warnings = $execution->getWarnings());
        self::assertSame('Skipped for test purpose', $warnings[0]->getMessage());
        self::assertSame(['itemIndex' => 0, 'item' => 'one'], $warnings[0]->getContext());
        self::assertSame('Skipped for test purpose', $warnings[1]->getMessage());
        self::assertSame(['itemIndex' => 1, 'item' => 'two'], $warnings[1]->getContext());
        self::assertSame('Skipped for test purpose', $warnings[2]->getMessage());
        self::assertSame(['itemIndex' => 2, 'item' => 'three'], $warnings[2]->getContext());
        self::assertStringContainsString('Skipping item in writer transformation 0.', (string)$execution->getLogs());
        self::assertStringContainsString('Skipping item in writer transformation 1.', (string)$execution->getLogs());
        self::assertStringContainsString('Skipping item in writer transformation 2.', (string)$execution->getLogs());
    }

    public function testInvalidIndexType(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Expecting argument to be string|int, but got null.');

        $writer = new TransformingWriter(new NullProcessor(), new InMemoryWriter());
        $generator = function () {
            yield null => null;
        };

        $writer->write($generator());
    }
}
