<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Storage;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Yokai\Batch\BatchStatus;
use Yokai\Batch\Exception\CannotRemoveJobExecutionException;
use Yokai\Batch\Exception\CannotStoreJobExecutionException;
use Yokai\Batch\Exception\JobExecutionNotFoundException;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Serializer\JobExecutionSerializerInterface;
use Yokai\Batch\Serializer\JsonJobExecutionSerializer;
use Yokai\Batch\Storage\FilesystemJobExecutionStorage;
use Yokai\Batch\Storage\Query;
use Yokai\Batch\Storage\QueryBuilder;
use Yokai\Batch\Test\Storage\JobExecutionStorageTestTrait;

class FilesystemJobExecutionStorageTest extends TestCase
{
    use ProphecyTrait;
    use JobExecutionStorageTestTrait;

    private const STORAGE_DIR = ARTIFACT_DIR . '/filesystem-storage';
    private const READONLY_STORAGE_DIR = ARTIFACT_DIR . '/filesystem-storage-readonly';

    /**
     * @var JobExecutionSerializerInterface|ObjectProphecy
     */
    private $serializer;

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
        $this->serializer = $this->prophesize(JobExecutionSerializerInterface::class);
        $this->serializer->extension()
            ->willReturn('txt');
    }

    private function createStorage(
        string $dir = self::STORAGE_DIR,
        JobExecutionSerializerInterface $serializer = null
    ): FilesystemJobExecutionStorage {
        return new FilesystemJobExecutionStorage(
            $serializer ?? $this->serializer->reveal(),
            $dir
        );
    }

    public function testStore(): void
    {
        $jobExecution = JobExecution::createRoot('123456789', 'export');

        $this->serializer->serialize($jobExecution)
            ->shouldBeCalledTimes(1)
            ->willReturn('serialized job execution');

        $this->createStorage()->store($jobExecution);

        $file = self::STORAGE_DIR . '/export/123456789.txt';
        self::assertFileExists($file);
        self::assertIsReadable($file);
        self::assertEquals('serialized job execution', file_get_contents($file));
    }

    public function testStoreFileNotWritable(): void
    {
        $this->expectException(CannotStoreJobExecutionException::class);

        $jobExecution = JobExecution::createRoot('123456789', 'export');
        $this->serializer->serialize($jobExecution)
            ->shouldBeCalledTimes(1)
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
        file_put_contents(self::STORAGE_DIR . '/export/123456789.txt', 'serialized and stored job execution');

        $this->serializer->unserialize('serialized and stored job execution')
            ->shouldBeCalledTimes(1)
            ->willReturn($jobExecution);

        self::assertSame($jobExecution, $this->createStorage()->retrieve('export', '123456789'));
    }

    /**
     * @dataProvider list
     */
    public function testList(string $jobName, array $expectedCouples): void
    {
        $storage = $this->createStorage(
            __DIR__ . '/fixtures/filesystem-job-execution',
            new JsonJobExecutionSerializer()
        );

        self::assertExecutions($expectedCouples, $storage->list($jobName));
    }

    public function list(): \Generator
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

    /**
     * @dataProvider query
     */
    public function testQueryWithProvider(QueryBuilder $query, array $expectedCouples): void
    {
        $storage = $this->createStorage(
            __DIR__ . '/fixtures/filesystem-job-execution',
            new JsonJobExecutionSerializer()
        );

        self::assertExecutions($expectedCouples, $storage->query($query->getQuery()));
    }

    public function query(): \Generator
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

        $this->serializer->serialize($jobExecution)
            ->shouldBeCalledTimes(1)
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
}
