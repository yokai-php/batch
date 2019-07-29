<?php

namespace Yokai\Batch\Tests\Unit\Bridge\Doctrine\DBAL;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Generator;
use PHPUnit\Framework\TestCase;
use Yokai\Batch\BatchStatus;
use Yokai\Batch\Bridge\Doctrine\DBAL\DoctrineDBALJobExecutionStorage;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Storage\Query;
use Yokai\Batch\Storage\QueryBuilder;

class DoctrineDBALJobExecutionStorageTest extends TestCase
{
    private const TABLE = 'yokai_batch_job_execution';
    private const COLUMNS = [
        'id',
        'job_name',
        'status',
        'parameters',
        'start_time',
        'end_time',
        'summary',
        'failures',
        'warnings',
        'child_executions',
    ];

    /**
     * @var Connection
     */
    private $connection;

    protected function setUp(): void
    {
        $this->connection = DriverManager::getConnection(['url' => getenv('DATABASE_URL')]);
    }

    protected function tearDown(): void
    {
        unset($this->connection);
    }

    private function createStorage(array $options = []): DoctrineDBALJobExecutionStorage
    {
        return new DoctrineDBALJobExecutionStorage($this->connection, $options);
    }

    public function testCreateStandardTable(): void
    {
        $schemaManager = $this->connection->getSchemaManager();

        self::assertFalse($schemaManager->tablesExist([self::TABLE]));
        $this->createStorage()->createTable();
        self::assertTrue($schemaManager->tablesExist([self::TABLE]));

        $columns = $schemaManager->listTableColumns(self::TABLE);
        self::assertEquals(self::COLUMNS, array_keys($columns));
    }

    public function testCreateCustomTable(): void
    {
        $table = 'acme_job_executions';
        $cols = [
            'uid',
            'job',
            'state',
            'config',
            'started',
            'ended',
            'info',
            'errors',
            'warns',
            'children',
        ];

        $options = array_merge(
            ['table' => $table],
            array_combine(
                [
                    'id_col',
                    'job_name_col',
                    'status_col',
                    'parameters_col',
                    'start_time_col',
                    'end_time_col',
                    'summary_col',
                    'failures_col',
                    'warnings_col',
                    'child_executions_col',
                ],
                $cols
            )
        );

        $schemaManager = $this->connection->getSchemaManager();

        self::assertFalse($schemaManager->tablesExist([$table]));
        $this->createStorage($options)->createTable();
        self::assertTrue($schemaManager->tablesExist([$table]));

        $columns = $schemaManager->listTableColumns($table);
        self::assertEquals($cols, array_keys($columns));
    }

    public function testStoreInsert(): void
    {
        $storage = $this->createStorage();
        $storage->createTable();
        $storage->store($execution = JobExecution::createRoot('123', 'export'));

        $retrievedExecution = $storage->retrieve('export', '123');
        self::assertSame('export', $retrievedExecution->getJobName());
        self::assertSame('123', $retrievedExecution->getId());
        self::assertSame(BatchStatus::PENDING, $retrievedExecution->getStatus()->getValue());
    }

    public function testStoreUpdate(): void
    {
        $storage = $this->createStorage();
        $storage->createTable();
        $storage->store($execution = JobExecution::createRoot('123', 'export'));
        $execution->setStatus(BatchStatus::COMPLETED);
        $storage->store($execution);

        $retrievedExecution = $storage->retrieve('export', '123');
        self::assertSame('export', $retrievedExecution->getJobName());
        self::assertSame('123', $retrievedExecution->getId());
        self::assertSame(BatchStatus::COMPLETED, $retrievedExecution->getStatus()->getValue());
    }

    public function testRetrieve(): void
    {
        $storage = $this->createStorage();
        $storage->createTable();
        $storage->store(JobExecution::createRoot('123', 'export'));
        $storage->store(JobExecution::createRoot('456', 'import'));

        $execution123 = $storage->retrieve('export', '123');
        self::assertSame('export', $execution123->getJobName());
        self::assertSame('123', $execution123->getId());

        $execution456 = $storage->retrieve('import', '456');
        self::assertSame('import', $execution456->getJobName());
        self::assertSame('456', $execution456->getId());
    }

    /**
     * @expectedException \Yokai\Batch\Exception\JobExecutionNotFoundException
     */
    public function testRetrieveNotFound(): void
    {
        $storage = $this->createStorage();
        $storage->createTable();
        $storage->store(JobExecution::createRoot('123', 'export'));

        $storage->retrieve('export', '456');
    }

    public function testList(): void
    {
        $storage = $this->createStorage();
        $storage->createTable();
        $this->loadFixtures($storage);

        self::assertExecutionIds(['123'], $storage->list('export'));
        self::assertExecutionIds(['456', '789', '987'], $storage->list('import'));
    }

    /**
     * @dataProvider queries
     */
    public function testQuery(QueryBuilder $queryBuilder, array $expected): void
    {
        $storage = $this->createStorage();
        $storage->createTable();
        $this->loadFixtures($storage);

        self::assertExecutionIds($expected, $storage->query($queryBuilder->getQuery()));
    }

    public function queries(): Generator
    {
        yield 'All' => [
            new QueryBuilder(),
            ['123', '456', '789', '987']
        ];

        yield 'By id : 123 & 789' => [
            (new QueryBuilder())
                ->ids(['123', '789']),
            ['123', '789']
        ];

        yield 'Pending' => [
            (new QueryBuilder())
                ->statuses([BatchStatus::PENDING]),
            ['987']
        ];

        yield 'Completed & Failed started long ago' => [
            (new QueryBuilder())
                ->statuses([BatchStatus::COMPLETED, BatchStatus::FAILED])
                ->sort(Query::SORT_BY_START_ASC),
            ['123', '456']
        ];
    }

    public static function assertExecutionIds(array $ids, iterable $executions): void
    {
        $actualIds = [];
        /** @var JobExecution $execution */
        foreach ($executions as $execution) {
            self::assertInstanceOf(JobExecution::class, $execution);
            $actualIds[] = $execution->getId();
        }

        self::assertSame($ids, $actualIds);
    }

    private function loadFixtures(DoctrineDBALJobExecutionStorage $storage): void
    {
        // completed export started at 2019-07-01 13:00 and ended at 2019-07-01 13:30
        $completedExport = JobExecution::createRoot('123', 'export', new BatchStatus(BatchStatus::COMPLETED));
        $completedExport->setStartTime(\DateTimeImmutable::createFromFormat(DATE_ISO8601, '2019-07-01T13:00:00+0200'));
        $completedExport->setEndTime(\DateTimeImmutable::createFromFormat(DATE_ISO8601, '2019-07-01T13:30:00+0200'));
        $storage->store($completedExport);

        // failed import started at 2019-07-01 17:30 and ended at 2019-07-01 18:30
        $failedImport = JobExecution::createRoot('456', 'import', new BatchStatus(BatchStatus::FAILED));
        $failedImport->setStartTime(\DateTimeImmutable::createFromFormat(DATE_ISO8601, '2019-07-01T17:30:00+0200'));
        $failedImport->setEndTime(\DateTimeImmutable::createFromFormat(DATE_ISO8601, '2019-07-01T18:30:00+0200'));
        $storage->store($failedImport);

        // running import started at 2019-06-30 22:00 and not ended
        $runningImport = JobExecution::createRoot('789', 'import', new BatchStatus(BatchStatus::RUNNING));
        $runningImport->setStartTime(\DateTimeImmutable::createFromFormat(DATE_ISO8601, '2019-06-30T22:00:00+0200'));
        $storage->store($runningImport);

        // pending import not started and not ended
        $pendingImport = JobExecution::createRoot('987', 'import', new BatchStatus(BatchStatus::PENDING));
        $storage->store($pendingImport);
    }
}
