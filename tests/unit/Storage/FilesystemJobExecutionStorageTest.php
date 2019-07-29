<?php

namespace Yokai\Batch\Tests\Unit\Storage;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Serializer\JobExecutionSerializerInterface;
use Yokai\Batch\Storage\FilesystemJobExecutionStorage;

class FilesystemJobExecutionStorageTest extends TestCase
{
    private const STORAGE_DIR = UNIT_ARTIFACT_DIR.'/filesystem-storage';

    /**
     * @var JobExecutionSerializerInterface|ObjectProphecy
     */
    private $serializer;

    protected function setUp()
    {
        $this->serializer = $this->prophesize(JobExecutionSerializerInterface::class);
    }

    protected function tearDown()
    {
        unset($this->serializer);
    }

    private function createStorage(string $dir = self::STORAGE_DIR, string $extension = 'txt')
    {
        return new FilesystemJobExecutionStorage(
            $this->serializer->reveal(),
            $dir,
            $extension
        );
    }

    public function testStore(): void
    {
        $jobExecution = JobExecution::createRoot('123456789', 'export');

        $this->serializer->serialize($jobExecution)
            ->shouldBeCalledTimes(1)
            ->willReturn('serialized job execution');

        $this->createStorage()->store($jobExecution);

        $file = self::STORAGE_DIR.'/export/123456789.txt';
        self::assertFileExists($file);
        self::assertIsReadable($file);
        self::assertEquals('serialized job execution', file_get_contents($file));
    }

    /**
     * @expectedException \Yokai\Batch\Exception\CannotStoreJobExecutionException
     */
    public function testStoreFileExists(): void
    {
        $jobExecution = JobExecution::createRoot('123456789', 'export');

        $this->createStorage('/path/not/found')->store($jobExecution);
    }

    public function testRetrieve(): void
    {
        $jobExecution = JobExecution::createRoot('123456789', 'export');
        file_put_contents(self::STORAGE_DIR.'/export/123456789.txt', 'serialized and stored job execution');

        $this->serializer->unserialize('serialized and stored job execution')
            ->shouldBeCalledTimes(1)
            ->willReturn($jobExecution);

        self::assertSame($jobExecution, $this->createStorage()->retrieve('export', '123456789'));
    }

    public function testList(): void
    {
        $list = [];
        $expected = [];
        foreach (['import', 'export'] as $jobName) {
            @mkdir(self::STORAGE_DIR."/$jobName", 0755, true);
            foreach (['123', '456'] as $executionId) {
                $list[] = $execution = JobExecution::createRoot($executionId, $jobName);
                if ($jobName === 'export') {
                    $expected[] = $execution;
                }
                file_put_contents(self::STORAGE_DIR."/$jobName/$executionId.txt", "$jobName/$executionId");
                $this->serializer->unserialize("$jobName/$executionId")
                    ->shouldBeCalledTimes($jobName === 'export' ? 1 : 0)
                    ->willReturn($execution);
            }
        }

        /** @var \Iterator $exports */
        $exports = $this->createStorage()->list('export');
        self::assertInstanceOf(\Iterator::class, $exports);
        self::assertSame($expected, iterator_to_array($exports));
    }

    /**
     * @expectedException \Yokai\Batch\Exception\JobExecutionNotFoundException
     */
    public function testRetrieveFileNotFound(): void
    {
        $this->createStorage('/path/not/found')->retrieve('123456789', 'export');
    }
}
