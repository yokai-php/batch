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
use Yokai\Batch\Storage\FilesystemJobExecutionStorage;
use Yokai\Batch\Storage\Query;
use Yokai\Batch\Storage\QueryBuilder;

class FilesystemJobExecutionStorageTest extends TestCase
{
    use ProphecyTrait;

    private const STORAGE_DIR = ARTIFACT_DIR . '/filesystem-storage';

    /**
     * @var JobExecutionSerializerInterface|ObjectProphecy
     */
    private $serializer;

    protected function setUp(): void
    {
        $this->serializer = $this->prophesize(JobExecutionSerializerInterface::class);
        $this->serializer->extension()
            ->willReturn('txt');
    }

    protected function tearDown(): void
    {
        unset($this->serializer);
    }

    private function createStorage(string $dir = self::STORAGE_DIR)
    {
        return new FilesystemJobExecutionStorage(
            $this->serializer->reveal(),
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

    public function testList(): void
    {
        $dir = self::STORAGE_DIR . '/list';

        $expected = [];
        foreach (['import', 'export'] as $jobName) {
            @mkdir($dir . "/$jobName", 0755, true);
            foreach (['123', '456'] as $executionId) {
                $execution = JobExecution::createRoot($executionId, $jobName);
                if ($jobName === 'export') {
                    $expected[] = $execution;
                }
                file_put_contents($dir . "/$jobName/$executionId.txt", "$jobName/$executionId");
                $this->serializer->unserialize("$jobName/$executionId")
                    ->shouldBeCalledTimes($jobName === 'export' ? 1 : 0)
                    ->willReturn($execution);
            }
        }

        /** @var \Iterator $exports */
        $exports = $this->createStorage($dir)->list('export');
        self::assertInstanceOf(\Iterator::class, $exports);
        self::assertSame($expected, iterator_to_array($exports));
    }

    public function testQuery(): void
    {
        $dir = self::STORAGE_DIR . '/query';
        $storage = $this->createStorage($dir);

        /** @var JobExecution[] $executions */
        $completedImport2017 = JobExecution::createRoot(
            '123',
            'query-import',
            new BatchStatus(BatchStatus::COMPLETED)
        );
        $completedImport2017->setStartTime(new \DateTimeImmutable('2017-01-01 12:00:00'));
        $completedImport2017->setEndTime(new \DateTimeImmutable('2017-01-01 13:00:00'));

        $failedImport2018 = JobExecution::createRoot(
            '456',
            'query-import',
            new BatchStatus(BatchStatus::FAILED)
        );
        $failedImport2018->setStartTime(new \DateTimeImmutable('2018-01-01 12:00:00'));
        $failedImport2018->setEndTime(new \DateTimeImmutable('2018-01-01 13:00:00'));

        $pendingExport2019 = JobExecution::createRoot(
            '123',
            'query-export',
            new BatchStatus(BatchStatus::PENDING)
        );

        $runningExport2020 = JobExecution::createRoot(
            '456',
            'query-export',
            new BatchStatus(BatchStatus::RUNNING)
        );
        $runningExport2020->setStartTime(new \DateTimeImmutable('2020-01-01 12:00:00'));

        $executions = [$completedImport2017, $failedImport2018, $pendingExport2019, $runningExport2020];
        foreach ($executions as $execution) {
            $jobName = $execution->getJobName();
            $executionId = $execution->getId();

            @mkdir($dir . "/$jobName", 0755, true);
            file_put_contents($dir . "/$jobName/$executionId.txt", "$jobName/$executionId");
            $this->serializer->unserialize("$jobName/$executionId")
                ->willReturn($execution);
        }

        $runningOrPendingRecentlyStarted = $storage->query(
            (new QueryBuilder())
                ->statuses([BatchStatus::RUNNING, BatchStatus::PENDING])
                ->sort(Query::SORT_BY_START_DESC)
                ->getQuery()
        );
        self::assertIsArray($runningOrPendingRecentlyStarted);
        self::assertSame([$runningExport2020, $pendingExport2019], $runningOrPendingRecentlyStarted);

        $exports = $storage->query(
            (new QueryBuilder())
                ->jobs(['query-export'])
                ->getQuery()
        );
        self::assertIsArray($exports);
        self::assertSame([$pendingExport2019, $runningExport2020], $exports);

        $id123 = $storage->query(
            (new QueryBuilder())
                ->ids(['123'])
                ->limit(3, 0)
                ->getQuery()
        );
        self::assertIsArray($id123);
        self::assertSame([$pendingExport2019, $completedImport2017], $id123);
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
}
