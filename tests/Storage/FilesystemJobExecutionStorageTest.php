<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Storage;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Yokai\Batch\BatchStatus;
use Yokai\Batch\Exception\CannotRemoveJobExecutionException;
use Yokai\Batch\Exception\CannotStoreJobExecutionException;
use Yokai\Batch\Exception\JobExecutionNotFoundException;
use Yokai\Batch\Factory\JobExecutionLoggerFactory\InMemoryJobExecutionLoggerFactory;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Serializer\JobExecutionSerializerInterface;
use Yokai\Batch\Serializer\JsonJobExecutionSerializer;
use Yokai\Batch\Storage\FilesystemJobExecutionStorage;
use Yokai\Batch\Storage\Query;
use Yokai\Batch\Storage\QueryBuilder;
use Yokai\Batch\Test\Storage\JobExecutionStorageTestTrait;

final class FilesystemJobExecutionStorageTest extends TestCase
{
    use JobExecutionStorageTestTrait;

    private const STORAGE_DIR = ARTIFACT_DIR . '/filesystem-storage';
    private const READONLY_STORAGE_DIR = ARTIFACT_DIR . '/filesystem-storage-readonly';

    private MockObject&JobExecutionSerializerInterface $serializer;

    public static function setUpBeforeClass(): void
    {
        \mkdir(self::READONLY_STORAGE_DIR);
        \mkdir(self::READONLY_STORAGE_DIR . '/export');
        \file_put_contents(self::READONLY_STORAGE_DIR . '/export/123456789.txt', 'export/123456789');
        \chmod(self::READONLY_STORAGE_DIR . '/export/123456789.txt', 555);
        \chmod(self::READONLY_STORAGE_DIR . '/export', 0555);
    }

    protected function setUp(): void
    {
        $this->serializer = $this->createMock(JobExecutionSerializerInterface::class);
        $this->serializer->method('extension')
            ->with()
            ->willReturn('txt');
    }

    private function createStorage(
        string $dir = self::STORAGE_DIR,
        JobExecutionSerializerInterface|null $serializer = null,
    ): FilesystemJobExecutionStorage {
        return new FilesystemJobExecutionStorage(
            $serializer ?? $this->serializer,
            $dir,
        );
    }

    public function testStore(): void
    {
        $jobExecution = JobExecution::createRoot('123456789', 'export');

        $this->serializer->method('serialize')
            ->with($jobExecution)
            ->willReturn('serialized job execution');

        $this->createStorage()->store($jobExecution);

        $file = self::STORAGE_DIR . '/export/123456789.txt';
        self::assertFileExists($file);
        self::assertIsReadable($file);
        self::assertSame('serialized job execution', \file_get_contents($file));
    }

    public function testStoreFileNotWritable(): void
    {
        $this->expectException(CannotStoreJobExecutionException::class);

        $jobExecution = JobExecution::createRoot('123456789', 'export');
        $this->serializer->method('serialize')
            ->with($jobExecution)
            ->willReturn('serialized job execution');

        $this->createStorage(self::READONLY_STORAGE_DIR)->store($jobExecution);
    }

    public function testStoreFilePathNotFound(): void
    {
        $this->expectException(CannotStoreJobExecutionException::class);

        $jobExecution = JobExecution::createRoot('123456789', 'export');

        $this->createStorage('/path/not/found')->store($jobExecution);
    }

    public function testRetrieve(): void
    {
        $jobExecution = JobExecution::createRoot('123456789', 'export');
        \file_put_contents(self::STORAGE_DIR . '/export/123456789.txt', 'serialized and stored job execution');

        $this->serializer->method('unserialize')
            ->with('serialized and stored job execution')
            ->willReturn($jobExecution);

        self::assertSame($jobExecution, $this->createStorage()->retrieve('export', '123456789'));
    }

    #[DataProvider('list')]
    public function testList(string $jobName, array $expectedCouples): void
    {
        $storage = $this->createStorage(
            __DIR__ . '/fixtures/filesystem-job-execution',
            new JsonJobExecutionSerializer(new InMemoryJobExecutionLoggerFactory()),
        );

        self::assertExecutions($expectedCouples, $storage->list($jobName));
    }

    public static function list(): \Generator
    {
        yield [
            'export',
            [
                ['export', '20210920'],
                ['export', '20210922'],
            ],
        ];
        yield [
            'list',
            [
                ['list', '20210910'],
                ['list', '20210915'],
                ['list', '20210920'],
            ],
        ];
    }

    #[DataProvider('query')]
    public function testQueryWithProvider(QueryBuilder $query, array $expectedCouples): void
    {
        $storage = $this->createStorage(
            __DIR__ . '/fixtures/filesystem-job-execution',
            new JsonJobExecutionSerializer(new InMemoryJobExecutionLoggerFactory()),
        );

        self::assertExecutions($expectedCouples, $storage->query($query->getQuery()));
        self::assertCount($storage->count($query->getQuery()), $expectedCouples);
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
        yield 'Filter ids' => [
            (new QueryBuilder())
                ->ids(['20210920']),
            [
                ['export', '20210920'],
                ['list', '20210920'],
            ],
        ];
        yield 'Filter job names' => [
            (new QueryBuilder())
                ->jobs(['list']),
            [
                ['list', '20210910'],
                ['list', '20210915'],
                ['list', '20210920'],
            ],
        ];
        yield 'Filter statuses' => [
            (new QueryBuilder())
                ->statuses([BatchStatus::FAILED]),
            [
                ['list', '20210910'],
            ],
        ];
        yield 'Order by start ASC' => [
            (new QueryBuilder())
                ->sort(Query::SORT_BY_START_ASC),
            [
                ['list', '20210910'],
                ['list', '20210915'],
                ['export', '20210920'],
                ['list', '20210920'],
                ['export', '20210922'],
            ],
        ];
        yield 'Order by start DESC' => [
            (new QueryBuilder())
                ->sort(Query::SORT_BY_START_DESC),
            [
                ['export', '20210922'],
                ['list', '20210920'],
                ['export', '20210920'],
                ['list', '20210915'],
                ['list', '20210910'],
            ],
        ];
        yield 'Order by end ASC' => [
            (new QueryBuilder())
                ->sort(Query::SORT_BY_END_ASC),
            [
                ['list', '20210910'],
                ['list', '20210915'],
                ['export', '20210920'],
                ['list', '20210920'],
                ['export', '20210922'],
            ],
        ];
        yield 'Order by end DESC' => [
            (new QueryBuilder())
                ->sort(Query::SORT_BY_END_DESC),
            [
                ['export', '20210922'],
                ['list', '20210920'],
                ['export', '20210920'],
                ['list', '20210915'],
                ['list', '20210910'],
            ],
        ];
        yield 'Filter start time lower boundary' => [
            (new QueryBuilder())
                ->startTime(new \DateTimeImmutable('2021-09-20T10:35:48+0200'), null),
            [
                ['export', '20210920'],
                ['export', '20210922'],
                ['list', '20210920'],
            ],
        ];
        yield 'Filter start time upper boundary' => [
            (new QueryBuilder())
                ->startTime(null, new \DateTimeImmutable('2021-09-20T10:35:50+0200')),
            [
                ['export', '20210920'],
                ['list', '20210910'],
                ['list', '20210915'],
            ],
        ];
        yield 'Filter start time boundaries' => [
            (new QueryBuilder())
                ->startTime(
                    new \DateTimeImmutable('2021-09-20T10:35:48+0200'),
                    new \DateTimeImmutable('2021-09-20T10:35:50+0200'),
                ),
            [
                ['export', '20210920'],
            ],
        ];
        yield 'Filter end time lower boundary' => [
            (new QueryBuilder())
                ->endTime(new \DateTimeImmutable('2021-09-20T10:35:48+0200'), null),
            [
                ['export', '20210920'],
                ['export', '20210922'],
                ['list', '20210920'],
            ],
        ];
        yield 'Filter end time upper boundary' => [
            (new QueryBuilder())
                ->endTime(null, new \DateTimeImmutable('2021-09-20T10:35:50+0200')),
            [
                ['export', '20210920'],
                ['list', '20210910'],
                ['list', '20210915'],
            ],
        ];
        yield 'Filter end time boundaries' => [
            (new QueryBuilder())
                ->endTime(
                    new \DateTimeImmutable('2021-09-20T10:35:48+0200'),
                    new \DateTimeImmutable('2021-09-20T10:35:50+0200'),
                ),
            [
                ['export', '20210920'],
            ],
        ];
    }

    public function testCountIgnoresLimit(): void
    {
        $storage = $this->createStorage(
            __DIR__ . '/fixtures/filesystem-job-execution',
            new JsonJobExecutionSerializer(new InMemoryJobExecutionLoggerFactory()),
        );

        // Fixtures contain 5 executions in total.
        // With limit(2, 0), query() must return 2 results while count() must return 5.
        $query = (new QueryBuilder())->limit(2, 0)->getQuery();

        $results = [];
        foreach ($storage->query($query) as $execution) {
            $results[] = $execution;
        }
        self::assertCount(2, $results);
        self::assertSame(5, $storage->count($query));
    }

    public function testRetrieveFilePathNotFound(): void
    {
        $this->expectException(JobExecutionNotFoundException::class);

        $this->createStorage('/path/not/found')->retrieve('123456789', 'export');
    }

    public function testRemoveFilePathNotFound(): void
    {
        $this->expectException(CannotRemoveJobExecutionException::class);

        $jobExecution = JobExecution::createRoot('123456789', 'export');
        $this->createStorage('/path/not/found')->remove($jobExecution);
    }

    public function testRemove(): void
    {
        $jobExecution = JobExecution::createRoot('will_be_removed', 'export');

        $this->serializer->method('serialize')
            ->with($jobExecution)
            ->willReturn('serialized job execution');

        $path = self::STORAGE_DIR . '/export/will_be_removed.txt';
        $storage = $this->createStorage();
        self::assertFileDoesNotExist($path);
        $storage->store($jobExecution);
        self::assertFileExists($path);
        $storage->remove($jobExecution);
        self::assertFileDoesNotExist($path);
    }

    public function testRemoveFileNotWritable(): void
    {
        $this->expectException(CannotRemoveJobExecutionException::class);

        $jobExecution = JobExecution::createRoot('123456789', 'export');
        $this->createStorage(self::READONLY_STORAGE_DIR)->remove($jobExecution);
    }

    public function testPurge(): void
    {
        $storage = $this->createStorage(
            self::STORAGE_DIR . '/purge',
            new JsonJobExecutionSerializer(new InMemoryJobExecutionLoggerFactory()),
        );

        foreach (['20210920', '20210922'] as $id) {
            $storage->store(JobExecution::createRoot($id, 'export'));
        }
        foreach (['20210910', '20210915', '20210920'] as $id) {
            $storage->store(JobExecution::createRoot($id, 'list'));
        }

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
}
