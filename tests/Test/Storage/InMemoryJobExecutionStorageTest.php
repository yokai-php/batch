<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Test\Storage;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Yokai\Batch\BatchStatus;
use Yokai\Batch\Exception\CannotRemoveJobExecutionException;
use Yokai\Batch\Exception\JobExecutionNotFoundException;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Storage\QueryBuilder;
use Yokai\Batch\Storage\SortDirection;
use Yokai\Batch\Test\Storage\InMemoryJobExecutionStorage;
use Yokai\Batch\Test\Storage\JobExecutionStorageTestTrait;

final class InMemoryJobExecutionStorageTest extends TestCase
{
    use JobExecutionStorageTestTrait;

    /**
     * Build a storage seeded with 5 executions for query/list/count/purge tests:
     *
     * export/20210920 — Completed, start=2021-09-20T10:35:48, end=2021-09-20T10:36:00
     * export/20210922 — Completed, start=2021-09-22T10:35:48, end=2021-09-22T10:36:00
     * list/20210910   — Failed,    start=2021-09-10T10:35:47, end=2021-09-10T10:36:00
     * list/20210915   — Completed, start=2021-09-15T10:35:48, end=2021-09-15T10:36:00
     * list/20210920   — Running,   start=2021-09-20T10:35:51, end=2021-09-20T10:36:05
     */
    private static function createSeededStorage(): InMemoryJobExecutionStorage
    {
        $executions = [
            self::createExecution(
                'export',
                '20210920',
                BatchStatus::Completed,
                '2021-09-20T10:35:48+0200',
                '2021-09-20T10:36:00+0200',
            ),
            self::createExecution(
                'export',
                '20210922',
                BatchStatus::Completed,
                '2021-09-22T10:35:48+0200',
                '2021-09-22T10:36:00+0200',
            ),
            self::createExecution(
                'list',
                '20210910',
                BatchStatus::Failed,
                '2021-09-10T10:35:47+0200',
                '2021-09-10T10:36:00+0200',
            ),
            self::createExecution(
                'list',
                '20210915',
                BatchStatus::Completed,
                '2021-09-15T10:35:48+0200',
                '2021-09-15T10:36:00+0200',
            ),
            self::createExecution(
                'list',
                '20210920',
                BatchStatus::Running,
                '2021-09-20T10:35:51+0200',
                '2021-09-20T10:36:05+0200',
            ),
        ];

        return new InMemoryJobExecutionStorage(...$executions);
    }

    private static function createExecution(
        string $jobName,
        string $id,
        BatchStatus $status,
        string $startTime,
        string $endTime,
    ): JobExecution {
        $execution = JobExecution::createRoot($id, $jobName);
        $execution->setStatus($status);
        $execution->setStartTime(new \DateTimeImmutable($startTime));
        $execution->setEndTime(new \DateTimeImmutable($endTime));

        return $execution;
    }

    public function testRetrieve(): void
    {
        $storage = new InMemoryJobExecutionStorage($execution = JobExecution::createRoot('123', 'testing'));
        self::assertSame($execution, $storage->retrieve('testing', '123'));
    }

    public function testRetrieveNotFound(): void
    {
        $this->expectExceptionObject(new JobExecutionNotFoundException('testing', '456'));

        $storage = new InMemoryJobExecutionStorage(JobExecution::createRoot('123', 'testing'));
        $storage->retrieve('testing', '456');
    }

    public function testStore(): void
    {
        $storage = new InMemoryJobExecutionStorage($original = JobExecution::createRoot('123', 'testing'));
        self::assertSame($original, $storage->retrieve('testing', '123'));

        $replaced = JobExecution::createRoot('123', 'testing');
        $storage->store($replaced);
        self::assertSame($replaced, $storage->retrieve('testing', '123'));

        $new = JobExecution::createRoot('456', 'testing');
        $storage->store($new);
        self::assertSame($new, $storage->retrieve('testing', '456'));

        self::assertSame([$replaced, $new], $storage->getExecutions());
    }

    public function testRemove(): void
    {
        $this->expectExceptionObject(new JobExecutionNotFoundException('testing', '123'));

        $storage = new InMemoryJobExecutionStorage(JobExecution::createRoot('123', 'testing'));

        // it is not required that the execution is the same object,
        // only id & job name are important
        $storage->remove(JobExecution::createRoot('123', 'testing'));

        $storage->retrieve('testing', '123');
    }

    public function testRemoveNotFound(): void
    {
        $this->expectExceptionObject(new CannotRemoveJobExecutionException('testing', '123'));

        $storage = new InMemoryJobExecutionStorage();
        $storage->remove(JobExecution::createRoot('123', 'testing'));
    }

    #[DataProvider('list')]
    public function testList(string $jobName, array $expectedCouples): void
    {
        self::assertExecutions($expectedCouples, self::createSeededStorage()->list($jobName));
    }

    public static function list(): \Generator
    {
        yield 'export' => [
            'export',
            [
                ['export', '20210920'],
                ['export', '20210922'],
            ],
        ];
        yield 'list' => [
            'list',
            [
                ['list', '20210910'],
                ['list', '20210915'],
                ['list', '20210920'],
            ],
        ];
        yield 'unknown job' => [
            'unknown',
            [],
        ];
    }

    #[DataProvider('query')]
    public function testQuery(QueryBuilder $builder, array $expectedCouples): void
    {
        $storage = self::createSeededStorage();
        $query = $builder->getQuery();

        self::assertExecutions($expectedCouples, $storage->query($query));
    }

    public static function query(): \Generator
    {
        yield 'No filter' => [
            new QueryBuilder(),
            [
                ['export', '20210920'],
                ['export', '20210922'],
                ['list', '20210910'],
                ['list', '20210915'],
                ['list', '20210920'],
            ],
        ];
        yield 'Filter by ids' => [
            (new QueryBuilder())->ids(['20210920']),
            [
                ['export', '20210920'],
                ['list', '20210920'],
            ],
        ];
        yield 'Filter by job names' => [
            (new QueryBuilder())->jobs(['list']),
            [
                ['list', '20210910'],
                ['list', '20210915'],
                ['list', '20210920'],
            ],
        ];
        yield 'Filter by statuses' => [
            (new QueryBuilder())->statuses([BatchStatus::Failed]),
            [
                ['list', '20210910'],
            ],
        ];
        yield 'Filter by multiple statuses' => [
            (new QueryBuilder())->statuses([BatchStatus::Completed, BatchStatus::Running]),
            [
                ['export', '20210920'],
                ['export', '20210922'],
                ['list', '20210915'],
                ['list', '20210920'],
            ],
        ];
        yield 'Sort by start ASC' => [
            (new QueryBuilder())->sort(SortDirection::StartAsc),
            [
                ['list', '20210910'],
                ['list', '20210915'],
                ['export', '20210920'],
                ['list', '20210920'],
                ['export', '20210922'],
            ],
        ];
        yield 'Sort by start DESC' => [
            (new QueryBuilder())->sort(SortDirection::StartDesc),
            [
                ['export', '20210922'],
                ['list', '20210920'],
                ['export', '20210920'],
                ['list', '20210915'],
                ['list', '20210910'],
            ],
        ];
        yield 'Sort by end ASC' => [
            (new QueryBuilder())->sort(SortDirection::EndAsc),
            [
                ['list', '20210910'],
                ['list', '20210915'],
                ['export', '20210920'],
                ['list', '20210920'],
                ['export', '20210922'],
            ],
        ];
        yield 'Sort by end DESC' => [
            (new QueryBuilder())->sort(SortDirection::EndDesc),
            [
                ['export', '20210922'],
                ['list', '20210920'],
                ['export', '20210920'],
                ['list', '20210915'],
                ['list', '20210910'],
            ],
        ];
        yield 'Filter start time lower boundary' => [
            (new QueryBuilder())->startTime(new \DateTimeImmutable('2021-09-20T10:35:48+0200'), null),
            [
                ['export', '20210920'],
                ['export', '20210922'],
                ['list', '20210920'],
            ],
        ];
        yield 'Filter start time upper boundary' => [
            (new QueryBuilder())->startTime(null, new \DateTimeImmutable('2021-09-20T10:35:50+0200')),
            [
                ['export', '20210920'],
                ['list', '20210910'],
                ['list', '20210915'],
            ],
        ];
        yield 'Filter start time both boundaries' => [
            (new QueryBuilder())->startTime(
                new \DateTimeImmutable('2021-09-20T10:35:48+0200'),
                new \DateTimeImmutable('2021-09-20T10:35:50+0200'),
            ),
            [
                ['export', '20210920'],
            ],
        ];
        yield 'Filter end time lower boundary' => [
            (new QueryBuilder())->endTime(new \DateTimeImmutable('2021-09-20T10:36:03+0200'), null),
            [
                ['export', '20210922'],
                ['list', '20210920'],
            ],
        ];
        yield 'Filter end time upper boundary' => [
            (new QueryBuilder())->endTime(null, new \DateTimeImmutable('2021-09-20T10:36:02+0200')),
            [
                ['export', '20210920'],
                ['list', '20210910'],
                ['list', '20210915'],
            ],
        ];
        yield 'Filter end time both boundaries' => [
            (new QueryBuilder())->endTime(
                new \DateTimeImmutable('2021-09-20T10:35:59+0200'),
                new \DateTimeImmutable('2021-09-20T10:36:02+0200'),
            ),
            [
                ['export', '20210920'],
            ],
        ];
        yield 'Limit' => [
            (new QueryBuilder())->limit(2, 0),
            [
                ['export', '20210920'],
                ['export', '20210922'],
            ],
        ];
        yield 'Offset' => [
            (new QueryBuilder())->limit(2, 2),
            [
                ['list', '20210910'],
                ['list', '20210915'],
            ],
        ];
    }

    public function testCountIgnoresLimit(): void
    {
        $storage = self::createSeededStorage();

        // With limit(2, 0), query() returns 2 results but count() returns total (5).
        $query = (new QueryBuilder())->limit(2, 0)->getQuery();

        $results = \iterator_to_array($storage->query($query));
        self::assertCount(2, $results);
        self::assertSame(5, $storage->count($query));
    }

    public function testCountWithFilter(): void
    {
        $storage = self::createSeededStorage();

        $query = (new QueryBuilder())->jobs(['list'])->getQuery();

        self::assertSame(3, $storage->count($query));
    }

    public function testPurge(): void
    {
        $storage = self::createSeededStorage();

        // limit is ignored by purge — all 3 "list" executions must be deleted
        $storage->purge((new QueryBuilder())->jobs(['list'])->limit(1, 0)->getQuery());

        self::assertExecutions(
            [
                ['export', '20210920'],
                ['export', '20210922'],
            ],
            $storage->query((new QueryBuilder())->getQuery()),
        );
    }

    public function testPurgeWithStatusFilter(): void
    {
        $storage = self::createSeededStorage();

        $storage->purge((new QueryBuilder())->statuses([BatchStatus::Completed])->getQuery());

        self::assertExecutions(
            [
                ['list', '20210910'],
                ['list', '20210920'],
            ],
            $storage->query((new QueryBuilder())->getQuery()),
        );
    }
}
