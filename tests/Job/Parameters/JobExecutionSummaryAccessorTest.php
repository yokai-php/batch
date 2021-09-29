<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Job\Parameters;

use PHPUnit\Framework\TestCase;
use Yokai\Batch\Exception\CannotAccessParameterException;
use Yokai\Batch\Job\Parameters\JobExecutionSummaryAccessor;
use Yokai\Batch\JobExecution;

class JobExecutionSummaryAccessorTest extends TestCase
{
    public function test(): void
    {
        $accessor = new JobExecutionSummaryAccessor('report');

        $execution = JobExecution::createRoot('123', 'testing');
        $execution->getSummary()->set('report', 42);
        self::assertSame(42, $accessor->get($execution));

        $execution = JobExecution::createRoot('123', 'testing');
        $execution->getSummary()->set('anything.else', 0);
        $execution->getSummary()->set('report', 1042);
        self::assertSame(1042, $accessor->get($execution));
    }

    public function testNotFound(): void
    {
        $this->expectException(CannotAccessParameterException::class);

        $accessor = new JobExecutionSummaryAccessor('an.undefined.summary.var');

        $accessor->get(JobExecution::createRoot('123', 'testing'));
    }
}
