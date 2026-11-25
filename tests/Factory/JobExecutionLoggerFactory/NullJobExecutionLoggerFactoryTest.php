<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Factory\JobExecutionLoggerFactory;

use PHPUnit\Framework\TestCase;
use Yokai\Batch\Factory\JobExecutionLoggerFactory\NullJobExecutionLoggerFactory;
use Yokai\Batch\Logger\NullJobExecutionLogger;

final class NullJobExecutionLoggerFactoryTest extends TestCase
{
    public function testCreateReturnsNullLogger(): void
    {
        $factory = new NullJobExecutionLoggerFactory();

        self::assertInstanceOf(NullJobExecutionLogger::class, $factory->create('job-execution-id'));
    }

    public function testRestoreReturnsNullLoggerRegardlessOfReference(): void
    {
        $factory = new NullJobExecutionLoggerFactory();

        $logger = $factory->restore('some stored reference');

        self::assertInstanceOf(NullJobExecutionLogger::class, $logger);
        self::assertSame('', $logger->getReference());
        self::assertSame([], \iterator_to_array($logger->getLogs()));
    }
}
