<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Job\Item\Writer;

use PHPUnit\Framework\TestCase;
use Yokai\Batch\Job\Item\Writer\ChainWriter;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Test\Job\Item\Writer\InMemoryWriter;
use Yokai\Batch\Test\Job\Item\Writer\TestDebugWriter;

class ChainWriterTest extends TestCase
{
    public function testWrite(): void
    {
        $chain = [
            new TestDebugWriter($writer1 = new InMemoryWriter()),
            new TestDebugWriter($writer2 = new InMemoryWriter()),
        ];

        $writer = new ChainWriter($chain);
        $writer->setJobExecution(JobExecution::createRoot('123456789', 'export'));
        $writer->initialize();
        $writer->write([1, 2, 3]);
        $writer->write([4, 5, 6]);
        $writer->flush();

        self::assertSame([1, 2, 3, 4, 5, 6], $writer1->getItems());
        self::assertSame([1, 2, 3, 4, 5, 6], $writer2->getItems());

        foreach ($chain as $decorated) {
            $decorated->assertWasConfigured();
            $decorated->assertWasUsed();
        }
    }
}
