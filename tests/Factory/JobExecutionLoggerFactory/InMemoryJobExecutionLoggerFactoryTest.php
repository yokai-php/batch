<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Factory\JobExecutionLoggerFactory;

use PHPUnit\Framework\TestCase;
use Yokai\Batch\Factory\JobExecutionLoggerFactory\InMemoryJobExecutionLoggerFactory;
use Yokai\Batch\Logger\InMemoryJobExecutionLogger;

final class InMemoryJobExecutionLoggerFactoryTest extends TestCase
{
    public function testCreatesJobExecutionLogger(): void
    {
        $factory = new InMemoryJobExecutionLoggerFactory();

        $logger = $factory->create('job-execution-id');

        self::assertInstanceOf(InMemoryJobExecutionLogger::class, $logger);
    }

    public function testCreatesDistinctInstancesEachCall(): void
    {
        $factory = new InMemoryJobExecutionLoggerFactory();

        $first = $factory->create('job-execution-id');
        $second = $factory->create('job-execution-id');

        self::assertNotSame($first, $second);
    }

    public function testLogsAreInitiallyEmpty(): void
    {
        $factory = new InMemoryJobExecutionLoggerFactory();

        $logger = $factory->create('job-execution-id');

        self::assertSame('', $logger->getReference());
        self::assertSame([], \iterator_to_array($logger->getLogs()));
    }

    public function testLogsAreIsolatedBetweenInstances(): void
    {
        $factory = new InMemoryJobExecutionLoggerFactory();

        $first = $factory->create('job-execution-id');
        $second = $factory->create('job-execution-id');

        $first->info('message for first');

        self::assertStringContainsString('message for first', $first->getReference());
        self::assertSame('', $second->getReference());
    }

    public function testRestoreLoadsExistingContent(): void
    {
        $factory = new InMemoryJobExecutionLoggerFactory();

        $existing = "line one\nline two";
        $logger = $factory->restore($existing);

        self::assertSame($existing, $logger->getReference());
    }

    public function testRestoreCreatesDistinctInstances(): void
    {
        $factory = new InMemoryJobExecutionLoggerFactory();

        $first = $factory->restore('some logs');
        $second = $factory->restore('some logs');

        self::assertNotSame($first, $second);
    }

    public function testRestoreFromEmptyReference(): void
    {
        $factory = new InMemoryJobExecutionLoggerFactory();

        $logger = $factory->restore('');

        self::assertSame('', $logger->getReference());
    }
}
