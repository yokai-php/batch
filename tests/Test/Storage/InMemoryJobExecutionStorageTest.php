<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Test\Storage;

use PHPUnit\Framework\TestCase;
use Yokai\Batch\Exception\CannotRemoveJobExecutionException;
use Yokai\Batch\Exception\JobExecutionNotFoundException;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Test\Storage\InMemoryJobExecutionStorage;

class InMemoryJobExecutionStorageTest extends TestCase
{
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
}
