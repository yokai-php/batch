<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Job\Item\Reader\Filesystem;

use PHPUnit\Framework\TestCase;
use Yokai\Batch\Exception\RuntimeException;
use Yokai\Batch\Job\Item\Reader\Filesystem\JsonLinesReader;
use Yokai\Batch\Job\Parameters\StaticValueParameterAccessor;
use Yokai\Batch\JobExecution;

class JsonLinesReaderTest extends TestCase
{
    public function testRead(): void
    {
        $reader = new JsonLinesReader(new StaticValueParameterAccessor(__DIR__ . '/fixtures/lines.jsonl'));
        $reader->setJobExecution(JobExecution::createRoot('123456', 'test'));

        self::assertSame(
            [
                ['object' => 'foo'],
                ['array', 'value'],
                'string',
                false,
                null,
                0,
            ],
            \iterator_to_array($reader->read())
        );
    }

    public function testReadUnknownFile(): void
    {
        $this->expectException(RuntimeException::class);
        $reader = new JsonLinesReader(new StaticValueParameterAccessor(__DIR__ . '/fixtures/unknown-file.ext'));
        $reader->setJobExecution(JobExecution::createRoot('123456', 'test'));
        \iterator_to_array($reader->read());
    }
}
