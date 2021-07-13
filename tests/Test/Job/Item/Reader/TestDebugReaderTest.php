<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Test\Job\Item\Reader;

use PHPUnit\Framework\TestCase;
use Yokai\Batch\Job\Item\Reader\StaticIterableReader;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Test\Job\Item\Reader\TestDebugReader;

class TestDebugReaderTest extends TestCase
{
    public function test(): void
    {
        $reader = new TestDebugReader(new StaticIterableReader([1, 2, 3]));
        self::assertFalse($reader->wasInitialized());
        self::assertFalse($reader->wasRead());
        self::assertFalse($reader->wasFlushed());

        $reader->setJobExecution(JobExecution::createRoot('123456', 'testing'));
        $reader->initialize();
        self::assertTrue($reader->wasInitialized());

        self::assertSame([1, 2, 3], $reader->read());
        self::assertTrue($reader->wasRead());

        $reader->flush();
        self::assertTrue($reader->wasFlushed());

        $reader->initialize();
        self::assertTrue($reader->wasInitialized());
        self::assertFalse($reader->wasRead());
        self::assertTrue($reader->wasFlushed());
    }
}
