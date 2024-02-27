<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Job;

use Exception;
use PHPUnit\Framework\TestCase;
use Yokai\Batch\Factory\UniqidJobExecutionIdGenerator;
use Yokai\Batch\JobExecution;

class LoggerTest extends TestCase
{
    public function testLogError()
    {
        $idGenerator = new UniqidJobExecutionIdGenerator();
        $errorToLog = 'test assert logErrorMethod';
        $errorException = 'test assert errorException';
        $jobExecution = JobExecution::createRoot($idGenerator->generate(), 'export');
        $jobExecution->logError(new Exception($errorException), $errorToLog);

        self::assertStringContainsString($errorToLog, $jobExecution->getLogs()->__toString());
        self::assertStringContainsString($errorException, $jobExecution->getLogs()->__toString());
    }
}
