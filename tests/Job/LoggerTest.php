<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Job;

use Exception;
use PHPUnit\Framework\TestCase;
use Yokai\Batch\Factory\UniqidJobExecutionIdGenerator;
use Yokai\Batch\JobExecution;

final class LoggerTest extends TestCase
{
    public function testLogError(): void
    {
        $idGenerator = new UniqidJobExecutionIdGenerator();
        $errorToLog = 'test assert logErrorMethod';
        $errorException = 'test assert errorException';
        $jobExecution = JobExecution::createRoot($idGenerator->generate(), 'export');
        $jobExecution->logError(new Exception($errorException), $errorToLog);

        $logs = $jobExecution->getLogger()->getLogsContent();
        self::assertStringContainsString($errorToLog, $logs);
        self::assertStringContainsString($errorException, $logs);
    }
}
