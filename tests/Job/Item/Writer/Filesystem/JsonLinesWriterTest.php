<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Job\Item\Writer\Filesystem;

use PHPUnit\Framework\TestCase;
use Yokai\Batch\Exception\RuntimeException;
use Yokai\Batch\Job\Item\Writer\Filesystem\JsonLinesWriter;
use Yokai\Batch\Job\Parameters\StaticValueParameterAccessor;
use Yokai\Batch\JobExecution;

class JsonLinesWriterTest extends TestCase
{
    private const WRITE_DIR = ARTIFACT_DIR . '/json-lines-writer';

    public static function setUpBeforeClass(): void
    {
        if (!\is_dir(self::WRITE_DIR)) {
            \mkdir(self::WRITE_DIR, 0777, true);
        }
    }

    public function testWrite(): void
    {
        $filename = self::WRITE_DIR . '/lines.jsonl';
        $writer = new JsonLinesWriter(new StaticValueParameterAccessor($filename));

        self::assertFileDoesNotExist($filename);

        $writer->setJobExecution(JobExecution::createRoot('123456', 'test'));
        $writer->initialize();
        $writer->write([
            ['object' => 'foo'],
            ['array', 'value'],
        ]);
        $writer->write([
            '"string"',
            false,
            null,
            0,
        ]);
        $writer->flush();

        self::assertFileExists($filename);
        self::assertSame(
            <<<JSONL
            {"object":"foo"}
            ["array","value"]
            "string"
            false
            null
            0
            JSONL,
            \trim(\file_get_contents($filename))
        );
    }

    public function testWriteUnknownDir(): void
    {
        $this->expectException(RuntimeException::class);
        $filename = '/path/to/unknown/dir/lines.jsonl';
        $writer = new JsonLinesWriter(new StaticValueParameterAccessor($filename));

        self::assertFileDoesNotExist($filename);

        $writer->setJobExecution(JobExecution::createRoot('123456', 'test'));
        $writer->initialize();
    }
}
