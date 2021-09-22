<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Test\Job\Item\Writer;

use PHPUnit\Framework\TestCase;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Test\Job\Item\Writer\InMemoryWriter;
use Yokai\Batch\Test\Job\Item\Writer\TestDebugWriter;

class TestDebugWriterTest extends TestCase
{
    public function test(): void
    {
        $writer = new TestDebugWriter($innerWriter = new InMemoryWriter());
        $writer->assertWasNotConfigured();
        $writer->assertWasNotUsed();

        $writer->configure(JobExecution::createRoot('123456', 'testing'));
        $writer->initialize();
        $writer->write([1, 2, 3]);
        $writer->write([4, 5, 6]);
        self::assertSame([1, 2, 3, 4, 5, 6], $innerWriter->getItems());
        self::assertSame([[1, 2, 3], [4, 5, 6]], $innerWriter->getBatchItems());
        $writer->flush();

        $writer->assertWasConfigured();
        $writer->assertWasUsed();
    }
}
