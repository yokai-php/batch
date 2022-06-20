<?php

declare(strict_types=1);

namespace Yokai\Batch\Test\Job\Item;

use PHPUnit\Framework\Assert;
use Yokai\Batch\Job\Item\ElementConfiguratorTrait;
use Yokai\Batch\Job\Item\FlushableInterface;
use Yokai\Batch\Job\Item\InitializableInterface;
use Yokai\Batch\Job\JobExecutionAwareInterface;
use Yokai\Batch\Job\JobParametersAwareInterface;
use Yokai\Batch\Job\SummaryAwareInterface;
use Yokai\Batch\JobExecution;
use Yokai\Batch\JobParameters;
use Yokai\Batch\Summary;

/**
 * This base contains all "runtime" interfaces handled by the lib.
 * It provides convenient assertion methods to ensure your component was used correctly.
 */
abstract class TestDebugComponent implements
    InitializableInterface,
    FlushableInterface,
    JobExecutionAwareInterface,
    JobParametersAwareInterface,
    SummaryAwareInterface
{
    use ElementConfiguratorTrait;

    private bool $initialized = false;
    private bool $flushed = false;
    private JobExecution $jobExecution;
    private bool $jobExecutionProvided = false;
    private bool $summaryProvided = false;
    private bool $jobParametersProvided = false;

    public function __construct(
        private object $decorated,
    ) {
    }

    /**
     * @internal
     * Utility method to simulate configuration from a {@see JobExecution}.
     */
    public function configure(JobExecution $jobExecution): void
    {
        $this->setJobExecution($jobExecution);
        $this->setJobParameters($jobExecution->getParameters());
        $this->setSummary($jobExecution->getSummary());
    }

    public function setJobExecution(JobExecution $jobExecution): void
    {
        $this->jobExecution = $jobExecution;
        $this->jobExecutionProvided = true;
    }

    public function setJobParameters(JobParameters $parameters): void
    {
        $this->jobParametersProvided = true;
    }

    public function setSummary(Summary $summary): void
    {
        $this->summaryProvided = true;
    }

    /**
     * Assert that component was provided with *Aware interfaces:
     * - {@see JobExecutionAwareInterface}
     * - {@see JobParametersAwareInterface}
     * - {@see SummaryAwareInterface}
     */
    public function assertWasConfigured(): void
    {
        Assert::assertTrue($this->jobExecutionProvided, 'Job execution was configured');
        Assert::assertTrue($this->jobParametersProvided, 'Job parameters were configured');
        Assert::assertTrue($this->summaryProvided, 'Summary was configured');
    }

    /**
     * Assert that component was not provided with *Aware interfaces:
     * - {@see JobExecutionAwareInterface}
     * - {@see JobParametersAwareInterface}
     * - {@see SummaryAwareInterface}
     */
    public function assertWasNotConfigured(): void
    {
        Assert::assertFalse($this->jobExecutionProvided, 'Job execution was not configured');
        Assert::assertFalse($this->jobParametersProvided, 'Job parameters were not configured');
        Assert::assertFalse($this->summaryProvided, 'Summary was not configured');
    }

    public function initialize(): void
    {
        $this->initialized = true;
        $this->initializeElement($this->decorated);
        $this->configureElementJobContext($this->decorated, $this->jobExecution);
    }

    public function flush(): void
    {
        $this->flushed = true;
        $this->flushElement($this->decorated);
    }

    /**
     * Assert that component was used (depends on extending class).
     * Also ensure that component was initialized & flushed.
     */
    public function assertWasUsed(): void
    {
        Assert::assertTrue($this->initialized, 'Element was initialized');
        Assert::assertTrue($this->wasUsed(), 'Element was used');
        Assert::assertTrue($this->flushed, 'Element was flushed');
    }

    /**
     * Assert that component was not used (depends on extending class).
     * Also ensure that component was (or was not) initialized & flushed.
     */
    public function assertWasNotUsed(bool $initialized = false, bool $flushed = false): void
    {
        Assert::assertSame(
            $initialized,
            $this->initialized,
            'Element was' . ($initialized ? ' not' : '') . ' initialized'
        );
        Assert::assertFalse($this->wasUsed(), 'Element was used');
        Assert::assertSame($flushed, $this->flushed, 'Element was' . ($flushed ? ' not' : '') . ' flushed');
    }

    abstract protected function wasUsed(): bool;
}
