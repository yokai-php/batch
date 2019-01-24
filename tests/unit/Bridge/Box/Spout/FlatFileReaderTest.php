<?php

namespace Yokai\Batch\Tests\Unit\Bridge\Box\Spout;

use Box\Spout\Common\Type;
use PHPUnit\Framework\TestCase;
use Yokai\Batch\Bridge\Box\Spout\FlatFileReader;
use Yokai\Batch\JobExecution;
use Yokai\Batch\JobParameters;

class FlatFileReaderTest extends TestCase
{
    /**
     * @dataProvider combination
     */
    public function testRead(string $type, string $headersMode, ?array $headers, array $expected)
    {
        $jobExecution = JobExecution::createRoot(
            '123456789',
            'parent',
            null,
            new JobParameters([FlatFileReader::SOURCE_FILE_PARAMETER => __DIR__ . '/fixtures/sample.' . $type])
        );
        $reader = new FlatFileReader($type, $headersMode, $headers);
        $reader->setJobExecution($jobExecution);

        /** @var \Iterator $got */
        $got = $reader->read();
        self::assertInstanceOf(\Iterator::class, $got);
        self::assertSame($expected, iterator_to_array($got));
    }

    /**
     * @dataProvider types
     * @expectedException \LogicException
     */
    public function testInvalidConstruction(string $type)
    {
        new FlatFileReader($type, FlatFileReader::HEADERS_MODE_COMBINE, ['nom', 'prenom']);
    }

    /**
     * @dataProvider types
     * @expectedException \Yokai\Batch\Exception\UndefinedJobParameterException
     */
    public function testMissingFileToRead(string $type)
    {
        $reader = new FlatFileReader($type);
        $reader->setJobExecution(JobExecution::createRoot('123456789', 'parent'));

        iterator_to_array($reader->read());
    }

    public function types()
    {
        foreach ([Type::CSV, Type::XLSX, Type::ODS] as $type) {
            yield [$type];
        }
    }

    public function combination()
    {
        foreach ($this->types() as list($type)) {
            yield [
                $type,
                FlatFileReader::HEADERS_MODE_NONE,
                null,
                [
                    ['firstName', 'lastName'],
                    ['John', 'Doe'],
                    ['Jane', 'Doe'],
                    ['Jack', 'Doe'],
                ],
            ];
            yield [
                $type,
                FlatFileReader::HEADERS_MODE_SKIP,
                null,
                [
                    ['John', 'Doe'],
                    ['Jane', 'Doe'],
                    ['Jack', 'Doe'],
                ],
            ];
            yield [
                $type,
                FlatFileReader::HEADERS_MODE_COMBINE,
                null,
                [
                    ['firstName' => 'John', 'lastName' => 'Doe'],
                    ['firstName' => 'Jane', 'lastName' => 'Doe'],
                    ['firstName' => 'Jack', 'lastName' => 'Doe'],
                ],
            ];

            yield [
                $type,
                FlatFileReader::HEADERS_MODE_NONE,
                ['prenom', 'nom'],
                [
                    ['prenom' => 'firstName', 'nom' => 'lastName'],
                    ['prenom' => 'John', 'nom' => 'Doe'],
                    ['prenom' => 'Jane', 'nom' => 'Doe'],
                    ['prenom' => 'Jack', 'nom' => 'Doe'],
                ],
            ];
            yield [
                $type,
                FlatFileReader::HEADERS_MODE_SKIP,
                ['prenom', 'nom'],
                [
                    ['prenom' => 'John', 'nom' => 'Doe'],
                    ['prenom' => 'Jane', 'nom' => 'Doe'],
                    ['prenom' => 'Jack', 'nom' => 'Doe'],
                ],
            ];
        }
    }
}
