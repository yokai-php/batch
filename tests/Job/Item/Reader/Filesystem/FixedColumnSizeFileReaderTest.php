<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Job\Item\Reader\Filesystem;

use Generator;
use PHPUnit\Framework\TestCase;
use Yokai\Batch\Exception\RuntimeException;
use Yokai\Batch\Exception\UnexpectedValueException;
use Yokai\Batch\Job\Item\Reader\Filesystem\FixedColumnSizeFileReader;
use Yokai\Batch\Job\Parameters\StaticValueParameterAccessor;
use Yokai\Batch\JobExecution;

class FixedColumnSizeFileReaderTest extends TestCase
{
    /**
     * @dataProvider config
     */
    public function test(array $columns, string $headersMode, array $expected): void
    {
        $execution = JobExecution::createRoot('123456', 'testing');
        $reader = new FixedColumnSizeFileReader(
            $columns,
            new StaticValueParameterAccessor(__DIR__ . '/fixtures/fixed-column-size.txt'),
            $headersMode
        );
        $reader->setJobExecution($execution);
        self::assertSame($expected, \iterator_to_array($reader->read()));
    }

    public function testInvalidHeaderMode(): void
    {
        $this->expectException(UnexpectedValueException::class);
        new FixedColumnSizeFileReader([10, 10], new StaticValueParameterAccessor(null), 'wrong header mode');
    }

    public function testFileNotFound(): void
    {
        $this->expectException(RuntimeException::class);
        $execution = JobExecution::createRoot('123456', 'testing');
        $reader = new FixedColumnSizeFileReader(
            [10, 10],
            new StaticValueParameterAccessor(__DIR__ . '/fixtures/unknown-file.ext')
        );
        $reader->setJobExecution($execution);
        \iterator_to_array($reader->read());
    }

    public function config(): Generator
    {
        $columnsWithoutNames = [10, 9, 8, -1];
        $columnsWithNames = ['firstName' => 10, 'lastName' => 9, 'country' => 8, 'city' => -1];

        yield [
            $columnsWithoutNames,
            FixedColumnSizeFileReader::HEADERS_MODE_COMBINE,
            [
                ['firstName' => 'John', 'lastName' => 'Doe', 'country' => 'USA', 'city' => 'Washington'],
                ['firstName' => 'Jane', 'lastName' => 'Doe', 'country' => 'USA', 'city' => 'Seattle'],
                ['firstName' => 'Jack', 'lastName' => 'Doe', 'country' => 'USA', 'city' => 'San Francisco'],
            ],
        ];
        yield [
            $columnsWithNames,
            FixedColumnSizeFileReader::HEADERS_MODE_SKIP,
            [
                ['firstName' => 'John', 'lastName' => 'Doe', 'country' => 'USA', 'city' => 'Washington'],
                ['firstName' => 'Jane', 'lastName' => 'Doe', 'country' => 'USA', 'city' => 'Seattle'],
                ['firstName' => 'Jack', 'lastName' => 'Doe', 'country' => 'USA', 'city' => 'San Francisco'],
            ],
        ];
        yield [
            $columnsWithoutNames,
            FixedColumnSizeFileReader::HEADERS_MODE_NONE,
            [
                ['firstName', 'lastName', 'country', 'city'],
                ['John', 'Doe', 'USA', 'Washington'],
                ['Jane', 'Doe', 'USA', 'Seattle'],
                ['Jack', 'Doe', 'USA', 'San Francisco'],
            ],
        ];
        yield [
            $columnsWithoutNames,
            FixedColumnSizeFileReader::HEADERS_MODE_SKIP,
            [
                ['John', 'Doe', 'USA', 'Washington'],
                ['Jane', 'Doe', 'USA', 'Seattle'],
                ['Jack', 'Doe', 'USA', 'San Francisco'],
            ],
        ];
    }
}
