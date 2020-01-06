<?php

namespace Yokai\Batch\Tests\Integration\Job;

use Box\Spout\Common\Entity\Row;
use Box\Spout\Common\Type;
use Box\Spout\Reader\Common\Creator\ReaderFactory;
use Box\Spout\Reader\SheetInterface;
use Yokai\Batch\Bridge\Box\Spout\FlatFileWriter;
use Yokai\Batch\Job\AbstractJob;
use Yokai\Batch\JobExecution;

final class SplitDeveloperXlsxJob extends AbstractJob
{
    private $inputFile;

    private $outputBadgeFile;

    private $outputRepositoryFile;

    private $outputDeveloperFile;

    public function __construct(
        string $inputFile,
        string $outputBadgeFile,
        string $outputRepositoryFile,
        string $outputDeveloperFile
    ) {
        $this->inputFile = $inputFile;
        $this->outputBadgeFile = $outputBadgeFile;
        $this->outputRepositoryFile = $outputRepositoryFile;
        $this->outputDeveloperFile = $outputDeveloperFile;
    }

    protected function doExecute(JobExecution $jobExecution): void
    {
        $badges = [];
        $repositories = [];
        $developers = [];

        $reader = ReaderFactory::createFromType(Type::XLSX);
        $reader->open($this->inputFile);
        $sheets = iterator_to_array($reader->getSheetIterator(), false);
        list($badgeSheet, $repositorySheet) = $sheets;

        foreach ($this->sheetToArray($badgeSheet) as $row) {
            list($firstName, $lastName, $badgeLabel, $badgeRank) = $row;

            $badgeData = ['label' => $badgeLabel, 'rank' => $badgeRank];
            $badgeKey = $badgeLabel;
            $developerData = ['firstName' => $firstName, 'lastName' => $lastName, 'badges' => [], 'repositories' => []];
            $developerKey = $firstName.'/'.$lastName;

            $badges[$badgeKey] = $badges[$badgeKey] ?? $badgeData;
            $developers[$developerKey] = $developers[$developerKey] ?? $developerData;
            $developers[$developerKey]['badges'][] = $badgeLabel;
        }

        foreach ($this->sheetToArray($repositorySheet) as $row) {
            list($firstName, $lastName, $repositoryLabel, $repositoryUrl) = $row;

            $repositoryData = ['label' => $repositoryLabel, 'url' => $repositoryUrl];
            $repositoryKey = $repositoryUrl;
            $developerData = ['firstName' => $firstName, 'lastName' => $lastName, 'badges' => [], 'repositories' => []];
            $developerKey = $firstName.'/'.$lastName;

            $repositories[$repositoryKey] = $repositories[$repositoryKey] ?? $repositoryData;
            $developers[$developerKey] = $developers[$developerKey] ?? $developerData;
            $developers[$developerKey]['repositories'][] = $repositoryUrl;
        }

        foreach ($developers as &$developer) {
            $developer['badges'] = implode('|', $developer['badges']);
            $developer['repositories'] = implode('|', $developer['repositories']);
        }

        $reader->close();

        $this->writeToCsv($this->outputBadgeFile, $badges, ['label', 'rank']);
        $this->writeToCsv($this->outputRepositoryFile, $repositories, ['label', 'url']);
        $this->writeToCsv($this->outputDeveloperFile, $developers, ['firstName', 'lastName', 'badges', 'repositories']);

        unset(
            $badges,
            $repositories,
            $developers
        );
    }

    private function writeToCsv(string $filename, array $data, array $headers): void
    {
        $writer = new FlatFileWriter(Type::CSV, $headers, $filename);
        $writer->initialize();
        $writer->write($data);
        $writer->flush();
    }

    private function sheetToArray(SheetInterface $sheet): array
    {
        return array_map(
            function ($row): array {
                if ($row instanceof Row) {
                    return $row->toArray();
                }

                return $row;
            },
            iterator_to_array(new \LimitIterator($sheet->getRowIterator(), 1), false)
        );
    }
}
