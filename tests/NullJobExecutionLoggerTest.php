<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests;

use PHPUnit\Framework\TestCase;
use Yokai\Batch\Logger\NullJobExecutionLogger;

final class NullJobExecutionLoggerTest extends TestCase
{
    public function testLogsAreDiscarded(): void
    {
        $logger = new NullJobExecutionLogger();

        $logger->debug('debug message');
        $logger->info('info message');
        $logger->warning('warning message');
        $logger->error('error message');

        self::assertSame('', $logger->getReference());
        self::assertSame([], \iterator_to_array($logger->getLogs()));
    }
}
