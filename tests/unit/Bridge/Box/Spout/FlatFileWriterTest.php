<?php

namespace Yokai\Batch\Tests\Unit\Bridge\Box\Spout;

use Box\Spout\Common\Type;
use Box\Spout\Reader\Wrapper\XMLReader;
use PHPUnit\Framework\TestCase;
use Yokai\Batch\Bridge\Box\Spout\FlatFileWriter;
use Yokai\Batch\JobExecution;
use Yokai\Batch\JobParameters;

class FlatFileWriterTest extends TestCase
{
    private const WRITE_DIR = UNIT_ARTIFACT_DIR.'/flat-file-writer';

    public static function setUpBeforeClass()
    {
        if (!is_dir(self::WRITE_DIR)) {
            mkdir(self::WRITE_DIR, 0777, true);
        }
    }

    /**
     * @dataProvider types
     * @expectedException \RuntimeException
     */
    public function testSomethingThatIsNotAnArray(string $type): void
    {
        $file = self::WRITE_DIR.'/not-an-array.'.$type;

        $writer = new FlatFileWriter($type);
        $writer->setJobExecution(
            JobExecution::createRoot(
                '123456789',
                'export',
                null,
                new JobParameters([FlatFileWriter::OUTPUT_FILE_PARAMETER => $file])
            )
        );

        $writer->initialize();
        $writer->write([true]);
    }

    /**
     * @dataProvider combination
     */
    public function testWrite(
        string $type,
        string $filename,
        ?array $headers,
        iterable $itemsToWrite,
        string $expectedContent
    ): void {
        $file = self::WRITE_DIR.'/'.$filename;

        self::assertFileNotExists($file);

        $writer = new FlatFileWriter($type, $headers);
        $writer->setJobExecution(
            JobExecution::createRoot(
                '123456789',
                'export',
                null,
                new JobParameters([FlatFileWriter::OUTPUT_FILE_PARAMETER => $file])
            )
        );

        $writer->initialize();
        $writer->write($itemsToWrite);
        $writer->flush();
        $this->assertFileContents($type, $file, $expectedContent);
    }

    public function types(): \Generator
    {
        foreach ([Type::CSV, Type::XLSX, Type::ODS] as $type) {
            yield [$type];
        }
    }

    public function combination(): \Generator
    {
        $headers = ['firstName', 'lastName'];
        $items = [
            ['John', 'Doe'],
            ['Jane', 'Doe'],
            ['Jack', 'Doe'],
        ];
        $content = <<<CSV
firstName,lastName
John,Doe
Jane,Doe
Jack,Doe
CSV;

        foreach ($this->types() as list($type)) {
            yield [
                $type,
                "header-in-items.$type",
                null,
                array_merge([$headers], $items),
                $content,
            ];
            yield [
                $type,
                "header-in-constructor.$type",
                $headers,
                $items,
                $content,
            ];
        }
    }

    private function assertFileContents(string $type, string $filePath, string $inlineData): void
    {
        $strings = array_merge(...array_map('str_getcsv', explode(PHP_EOL, $inlineData)));

        switch ($type) {
            case Type::CSV:
                $fileContents = file_get_contents($filePath);
                foreach ($strings as $string) {
                    self::assertContains($string, $fileContents);
                }
                break;

            case Type::XLSX:
                $pathToSheetFile = $filePath.'#xl/worksheets/sheet1.xml';
                $xmlContents = file_get_contents('zip://'.$pathToSheetFile);
                foreach ($strings as $string) {
                    self::assertContains($string, $xmlContents);
                }
                break;

            case Type::ODS:
                $xmlReader = new XMLReader();
                $xmlReader->openFileInZip($filePath, 'content.xml');
                $xmlReader->readUntilNodeFound('table:table');
                $sheetXmlAsString = $xmlReader->readOuterXml();
                foreach ($strings as $string) {
                    self::assertContains("<text:p>$string</text:p>", $sheetXmlAsString);
                }
                break;
        }
    }
}
