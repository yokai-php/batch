<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Job\Parameters;

use PHPUnit\Framework\TestCase;
use Yokai\Batch\Exception\CannotAccessParameterException;
use Yokai\Batch\Job\Parameters\JobExecutionParameterAccessor;
use Yokai\Batch\JobExecution;
use Yokai\Batch\JobParameters;

class JobExecutionParameterAccessorTest extends TestCase
{
    public function test(): void
    {
        $accessor = new JobExecutionParameterAccessor('since');

        $execution = JobExecution::createRoot('123', 'testing', null, new JobParameters(['since' => '2021-07-15']));
        self::assertSame('2021-07-15', $accessor->get($execution));

        $execution = JobExecution::createRoot('123', 'testing', null, new JobParameters(['since' => '2021-07-14']));
        self::assertSame('2021-07-14', $accessor->get($execution));
    }

    public function testJobParameterNotFound(): void
    {
        $this->expectException(CannotAccessParameterException::class);
        $accessor = new JobExecutionParameterAccessor('since');

        $execution = JobExecution::createRoot('123', 'testing', null, new JobParameters(['misnamed' => '2021-07-15']));
        $accessor->get($execution);
    }
}
