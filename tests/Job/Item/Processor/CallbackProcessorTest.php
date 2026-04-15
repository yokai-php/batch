<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Job\Item\Processor;

use PHPUnit\Framework\TestCase;
use Yokai\Batch\Job\Item\Processor\CallbackProcessor;
use Yokai\Batch\JobExecution;

final class CallbackProcessorTest extends TestCase
{
    public function testProcess(): void
    {
        $processor = new CallbackProcessor(fn($item, JobExecution $jobExecution)
            => \mb_strtolower($item . '-' . $jobExecution->getJobName()));
        $processor->setJobExecution(JobExecution::createRoot(id: '123', jobName: 'test'));
        self::assertSame('john-test', $processor->process('John'));
        self::assertSame('doe-test', $processor->process('DOE'));
    }
}
