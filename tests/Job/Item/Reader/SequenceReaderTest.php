<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Job\Item\Reader;

use PHPUnit\Framework\TestCase;
use Yokai\Batch\Job\Item\Reader\SequenceReader;
use Yokai\Batch\Job\Item\Reader\StaticIterableReader;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Test\Job\Item\Reader\TestDebugReader;

class SequenceReaderTest extends TestCase
{
    public function testRead(): void
    {
        $sequence = [
            new TestDebugReader(
                new StaticIterableReader([1, 2, 3])
            ),
            new TestDebugReader(
                new StaticIterableReader([4, 5, 6])
            ),
            new TestDebugReader(
                new StaticIterableReader([7, 8, 9])
            ),
        ];

        $reader = new SequenceReader($sequence);
        $reader->setJobExecution(JobExecution::createRoot('123456789', 'export'));
        $reader->initialize();
        $value = $reader->read();
        $reader->flush();

        self::assertInstanceOf(\Generator::class, $value);
        self::assertSame([1, 2, 3, 4, 5, 6, 7, 8, 9], \iterator_to_array($value));

        foreach ($sequence as $decorated) {
            $decorated->assertWasConfigured();
            $decorated->assertWasUsed();
        }
    }
}
