<?php

namespace Yokai\Batch\Tests\Integration;

use Box\Spout\Common\Type;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Yokai\Batch\Bridge\Box\Spout\FlatFileReader;
use Yokai\Batch\Bridge\Doctrine\Common\ObjectWriter;
use Yokai\Batch\Job\Item\ItemJob;
use Yokai\Batch\Job\JobInterface;
use Yokai\Batch\Job\JobWithChildJobs;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Storage\JobExecutionStorageInterface;
use Yokai\Batch\Tests\Integration\Entity\Badge;
use Yokai\Batch\Tests\Integration\Entity\Developer;
use Yokai\Batch\Tests\Integration\Entity\Repository;
use Yokai\Batch\Tests\Integration\Job\SplitDeveloperXlsxJob;
use Yokai\Batch\Tests\Integration\Processor\BadgeProcessor;
use Yokai\Batch\Tests\Integration\Processor\DeveloperProcessor;
use Yokai\Batch\Tests\Integration\Processor\RepositoryProcessor;

class ImportDevelopersXlsxToORMTest extends JobTestCase
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

    protected function createJob(JobExecutionStorageInterface $executionStorage): JobInterface
    {
        $entityManager = $this->entityManager;
        $objectWriter = new ObjectWriter($this->doctrine->reveal());

        $inputFile = self::INPUT_FILE;
        $outputBadgeFile = self::OUTPUT_BADGE_FILE;
        $outputRepositoryFile = self::OUTPUT_REPOSITORY_FILE;
        $outputDeveloperFile = self::OUTPUT_DEVELOPER_FILE;

        $csvReader = function (string $file): FlatFileReader {
            return new FlatFileReader(Type::CSV, [], FlatFileReader::HEADERS_MODE_COMBINE, null, $file);
        };

        return new JobWithChildJobs(
            $executionStorage,
            self::createJobRegistry([
                'split' => new SplitDeveloperXlsxJob(
                    $inputFile,
                    $outputBadgeFile,
                    $outputRepositoryFile,
                    $outputDeveloperFile
                ),
                'import' => new JobWithChildJobs(
                    $executionStorage,
                    self::createJobRegistry([
                        'import-badge' => new ItemJob(
                            PHP_INT_MAX,
                            $csvReader(self::OUTPUT_BADGE_FILE),
                            new BadgeProcessor(),
                            $objectWriter,
                            $executionStorage
                        ),
                        'import-repository' => new ItemJob(
                            PHP_INT_MAX,
                            $csvReader(self::OUTPUT_REPOSITORY_FILE),
                            new RepositoryProcessor(),
                            $objectWriter,
                            $executionStorage
                        ),
                        'import-developer' => new ItemJob(
                            5,
                            $csvReader(self::OUTPUT_DEVELOPER_FILE),
                            new DeveloperProcessor($entityManager),
                            $objectWriter,
                            $executionStorage
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

        self::assertFalse($jobExecution->getStatus()->isUnsuccessful());

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
