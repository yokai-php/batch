<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Job;

use Error;
use PHPUnit\Framework\TestCase;
use Yokai\Batch\JobExecution;
use Yokai\Batch\JobParameters;
use Yokai\Batch\Summary;

class ConfigurableElementTest extends TestCase
{
    public function testJobExecutionCanBeInitialized(): void
    {
        $element = new ConfigurableElement();
        $element->setJobExecution($execution = JobExecution::createRoot('123', 'testing'));
        self::assertSame($execution, $element->getJobExecution());
        self::assertSame($execution, $element->getRootExecution());
    }

    public function testJobExecutionMustBeInitialized(): void
    {
        $this->expectException(Error::class);
        (new ConfigurableElement())->getJobExecution();
    }

    public function testJobParametersMustCanInitialized(): void
    {
        $element = new ConfigurableElement();
        $element->setJobParameters($parameters = new JobParameters());
        self::assertSame($parameters, $element->getJobParameters());
    }

    public function testJobParametersMustBeInitialized(): void
    {
        $this->expectException(Error::class);
        (new ConfigurableElement())->getJobParameters();
    }

    public function testSummaryMustCanInitialized(): void
    {
        $element = new ConfigurableElement();
        $element->setSummary($summary = new Summary());
        self::assertSame($summary, $element->getSummary());
    }

    public function testSummaryMustBeInitialized(): void
    {
        $this->expectException(Error::class);
        (new ConfigurableElement())->getSummary();
    }
}
