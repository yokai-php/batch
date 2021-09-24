<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Storage;

use Yokai\Batch\Exception\JobExecutionNotFoundException;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Storage\NullJobExecutionStorage;
use PHPUnit\Framework\TestCase;

class NullJobExecutionStorageTest extends TestCase
{
    public function testNoStorage(): void
    {
        $storage = new NullJobExecutionStorage();
        $storage->store(JobExecution::createRoot('123', 'testing'));
        $storage->store($execution = JobExecution::createRoot('456', 'testing'));
        $storage->remove($execution);
        $storage->remove(JobExecution::createRoot('unknown', 'execution'));

        self::assertTrue(true); //no exception thrown in any case
    }

    public function testCannotRetrieve(): void
    {
        $this->expectException(JobExecutionNotFoundException::class);
        $storage = new NullJobExecutionStorage();
        $storage->store(JobExecution::createRoot('123', 'testing'));
        $storage->retrieve('testing', '123');
    }
}
