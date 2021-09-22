<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Job\Item\Reader;

use PHPUnit\Framework\TestCase;
use Yokai\Batch\Exception\UnexpectedValueException;
use Yokai\Batch\Job\Item\Reader\AddMetadataReader;
use Yokai\Batch\Job\Item\Reader\StaticIterableReader;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Test\Job\Item\Reader\TestDebugReader;

class AddMetadataReaderTest extends TestCase
{
    public function testRead(): void
    {
        $reader = new AddMetadataReader(
            $decorated = new TestDebugReader(
                new StaticIterableReader([['name' => 'John'], ['name' => 'Jane']])
            ),
            ['_type' => 'user']
        );

        $reader->setJobExecution(JobExecution::createRoot('123456', 'test'));
        $reader->initialize();
        $read = \iterator_to_array($reader->read());
        $reader->flush();

        self::assertSame(
            [['_type' => 'user', 'name' => 'John'], ['_type' => 'user', 'name' => 'Jane']],
            $read
        );

        $decorated->assertWasConfigured();
        $decorated->assertWasUsed();
    }

    public function testReadDecoratedNotReturningAnArray(): void
    {
        $this->expectException(UnexpectedValueException::class);

        $reader = new AddMetadataReader(
            new StaticIterableReader(['string']),
            ['_type' => 'user']
        );

        $reader->setJobExecution(JobExecution::createRoot('123456', 'test'));
        $reader->initialize();
        \iterator_to_array($reader->read());
    }
}
