<?php

namespace Yokai\Batch\Tests\Integration;

use Box\Spout\Common\Type;
use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Reader\SheetInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Yokai\Batch\Bridge\Box\Spout\FlatFileReader;
use Yokai\Batch\Bridge\Box\Spout\FlatFileWriter;
use Yokai\Batch\Bridge\Doctrine\Common\ObjectWriter;
use Yokai\Batch\Job\AbstractJob;
use Yokai\Batch\Job\Item\ItemJob;
use Yokai\Batch\Job\Item\ItemProcessorInterface;
use Yokai\Batch\Job\JobInterface;
use Yokai\Batch\Job\JobWithChildJobs;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Storage\JobExecutionStorageInterface;
use Yokai\Batch\Tests\Integration\Entity\Badge;
use Yokai\Batch\Tests\Integration\Entity\Developer;
use Yokai\Batch\Tests\Integration\Entity\Repository;

class MultiTabXlsxToObjectsTest extends JobTestCase
{
    private const OUTPUT_BASE_DIR = self::OUTPUT_DIR.'/multi-tab-xlsx-to-objects';
    private const OUTPUT_BADGE_FILE = self::OUTPUT_BASE_DIR.'/badge.csv';
    private const OUTPUT_REPOSITORY_FILE = self::OUTPUT_BASE_DIR.'/repository.csv';
    private const OUTPUT_DEVELOPER_FILE = self::OUTPUT_BASE_DIR.'/developer.csv';
    private const INPUT_FILE = __DIR__.'/fixtures/multi-tab-xlsx-to-objects.xslx';

    private $persisted;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var ManagerRegistry|ObjectProphecy
     */
    private $doctrine;

    protected function setUp(): void
    {
        $this->persisted = [];

        if (!is_dir(self::OUTPUT_BASE_DIR)) {
            mkdir(self::OUTPUT_BASE_DIR, 0777, true);
        }

        $config = Setup::createAnnotationMetadataConfiguration([__DIR__.'/Entity'], true, null, null, false);
        $this->entityManager = EntityManager::create(['url' => getenv('DATABASE_URL')], $config);

        (new SchemaTool($this->entityManager))
            ->createSchema($this->entityManager->getMetadataFactory()->getAllMetadata());

        $this->doctrine = $this->prophesize(ManagerRegistry::class);
        $this->doctrine->getManagerForClass(Argument::any())
            ->willReturn($this->entityManager);
    }

    protected function getJobName(): string
    {
        return 'multi-tab-xlsx-to-objects';
    }

    protected function createJob(): JobInterface
    {
        $entityManager = $this->entityManager;
        $objectWriter = new ObjectWriter($this->doctrine->reveal());

        $inputFile = self::INPUT_FILE;
        $outputBadgeFile = self::OUTPUT_BADGE_FILE;
        $outputRepositoryFile = self::OUTPUT_REPOSITORY_FILE;
        $outputDeveloperFile = self::OUTPUT_DEVELOPER_FILE;

        return new JobWithChildJobs(
            self::createJobRegistry([
                'split' => new class($inputFile, $outputBadgeFile, $outputRepositoryFile, $outputDeveloperFile) extends AbstractJob
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

                        $reader = ReaderFactory::create(Type::XLSX);
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
                            $developers,
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
                        return iterator_to_array(new \LimitIterator($sheet->getRowIterator(), 1), false);
                    }
                },
                'import' => new JobWithChildJobs(
                    self::createJobRegistry([
                        'import-badge' => new ItemJob(
                            PHP_INT_MAX,
                            new FlatFileReader(Type::CSV, FlatFileReader::HEADERS_MODE_COMBINE, null, self::OUTPUT_BADGE_FILE),
                            new class implements ItemProcessorInterface
                            {
                                public function process($item)
                                {
                                    $badge = new Badge();
                                    $badge->label = $item['label'];
                                    $badge->rank = $item['rank'];

                                    return $badge;
                                }
                            },
                            $objectWriter
                        ),
                        'import-repository' => new ItemJob(
                            PHP_INT_MAX,
                            new FlatFileReader(Type::CSV, FlatFileReader::HEADERS_MODE_COMBINE, null, self::OUTPUT_REPOSITORY_FILE),
                            new class implements ItemProcessorInterface
                            {
                                public function process($item)
                                {
                                    $repository = new Repository();
                                    $repository->label = $item['label'];
                                    $repository->url = $item['url'];

                                    return $repository;
                                }
                            },
                            $objectWriter
                        ),
                        'import-developer' => new ItemJob(
                            5,
                            new FlatFileReader(Type::CSV, FlatFileReader::HEADERS_MODE_COMBINE, null, self::OUTPUT_DEVELOPER_FILE),
                            new class ($entityManager) implements ItemProcessorInterface
                            {
                                private $manager;

                                public function __construct(EntityManager $manager)
                                {
                                    $this->manager = $manager;
                                }

                                public function process($item)
                                {
                                    $badges = $this->manager->getRepository(Badge::class)
                                        ->findBy(['label' => str_getcsv($item['badges'], '|')]);
                                    $repositories = $this->manager->getRepository(Repository::class)
                                        ->findBy(['label' => str_getcsv($item['repositories'], '|')]);

                                    $developer = new Developer();
                                    $developer->firstName = $item['firstName'];
                                    $developer->lastName = $item['lastName'];
                                    foreach ($badges as $badge) {
                                        $developer->badges->add($badge);
                                    }
                                    foreach ($repositories as $repository) {
                                        $developer->repositories->add($repository);
                                    }

                                    return $developer;
                                }
                            },
                            $objectWriter
                        ),
                    ]),
                    ['import-badge', 'import-repository', 'import-developer']
                ),
            ]),
            ['split', 'import']
        );
    }

    protected function assertAgainstExecution(
        JobExecutionStorageInterface $jobExecutionStorage,
        JobExecution $jobExecution
    ): void {
        parent::assertAgainstExecution($jobExecutionStorage, $jobExecution);

        $importJobExecution = $jobExecution->getChildExecution('import');

        $expectedCountBadges = 27;
        $importBadgeSummary = $importJobExecution->getChildExecution('import-badge')->getSummary();
        self::assertSame($expectedCountBadges, $importBadgeSummary->get('read'));
        self::assertSame($expectedCountBadges, $importBadgeSummary->get('processed'));
        self::assertSame($expectedCountBadges, $importBadgeSummary->get('write'));
        self::assertSame($expectedCountBadges, $this->entityManager->getRepository(Badge::class)->count([]));

        $expectedCountRepositories = 3;
        $importRepositorySummary = $importJobExecution->getChildExecution('import-repository')->getSummary();
        self::assertSame($expectedCountRepositories, $importRepositorySummary->get('read'));
        self::assertSame($expectedCountRepositories, $importRepositorySummary->get('processed'));
        self::assertSame($expectedCountRepositories, $importRepositorySummary->get('write'));
        self::assertSame($expectedCountRepositories, $this->entityManager->getRepository(Repository::class)->count([]));

        $expectedCountDevelopers = 20;
        $importDeveloperSummary = $importJobExecution->getChildExecution('import-developer')->getSummary();
        self::assertSame($expectedCountDevelopers, $importDeveloperSummary->get('read'));
        self::assertSame($expectedCountDevelopers, $importDeveloperSummary->get('processed'));
        self::assertSame($expectedCountDevelopers, $importDeveloperSummary->get('write'));
        self::assertSame($expectedCountDevelopers, $this->entityManager->getRepository(Developer::class)->count([]));
    }
}
