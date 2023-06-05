<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Test\Job\Item\Writer;

use PHPUnit\Framework\TestCase;
use Yokai\Batch\Test\Job\Item\Writer\InMemoryWriter;

class InMemoryWriterTest extends TestCase
{
    public function test(): void
    {
        $writer = new InMemoryWriter();
        self::assertSame([], $writer->getItems());
        $writer->initialize();
        self::assertSame([], $writer->getItems());
        $writer->write([1, 2, 3]);
        self::assertSame([1, 2, 3], $writer->getItems());
        $writer->write([4, 5, 6]);
        self::assertSame([1, 2, 3, 4, 5, 6], $writer->getItems());
        $writer->initialize();
        $writer->write([7, 8, 9]);
        self::assertSame([7, 8, 9], $writer->getItems());
    }
}
