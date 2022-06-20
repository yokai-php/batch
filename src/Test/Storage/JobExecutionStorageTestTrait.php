<?php

declare(strict_types=1);

namespace Yokai\Batch\Test\Storage;

use PHPUnit\Framework\Assert;
use Yokai\Batch\JobExecution;

/**
 * Handy methods for JobExecution storage test classes.
 */
trait JobExecutionStorageTestTrait
{
    private static function assertExecutions(array $expectedCouples, iterable $executions): void
    {
        $expected = [];
        foreach ($expectedCouples as [$jobName, $executionId]) {
            $expected[] = $jobName . '/' . $executionId;
        }

        $actual = [];
        /** @var JobExecution $execution */
        foreach ($executions as $execution) {
            $actual[] = $execution->getJobName() . '/' . $execution->getId();
        }

        Assert::assertSame($expected, $actual);
    }
}
