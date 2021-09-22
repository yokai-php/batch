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
        $reader->assertWasNotConfigured();
        $reader->assertWasNotUsed();

        $reader->configure(JobExecution::createRoot('123456', 'testing'));
        $reader->initialize();
        self::assertSame([1, 2, 3], $reader->read());
        $reader->flush();

        $reader->assertWasConfigured();
        $reader->assertWasUsed();
    }
}
