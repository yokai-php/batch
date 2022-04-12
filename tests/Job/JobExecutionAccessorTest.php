<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Job;

use PHPUnit\Framework\TestCase;
use Yokai\Batch\Factory\JobExecutionFactory;
use Yokai\Batch\Job\JobExecutionAccessor;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Test\Factory\SequenceJobExecutionIdGenerator;
use Yokai\Batch\Test\Storage\InMemoryJobExecutionStorage;

class JobExecutionAccessorTest extends TestCase
{
    public function test(): void
    {
        $accessor = new JobExecutionAccessor(
            new JobExecutionFactory(new SequenceJobExecutionIdGenerator(['123', '456'])),
            $storage = new InMemoryJobExecutionStorage(
                $existing = JobExecution::createRoot('abc', 'test')
            )
        );

        self::assertSame($existing, $accessor->get('test', ['_id' => 'abc']));

        $new = $accessor->get('test', ['foo' => 'FOO']);
        self::assertSame('123', $new->getId());
        self::assertSame('FOO', $new->getParameter('foo'));
        self::assertSame($new, $storage->retrieve('test', '123'));

        self::assertSame($new, $accessor->get('test', ['_id' => '123']));
    }
}
