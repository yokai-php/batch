<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Factory;

use PHPUnit\Framework\TestCase;
use Yokai\Batch\Factory\JobExecutionFactory;
use Yokai\Batch\Factory\UniqidJobExecutionIdGenerator;
use Yokai\Batch\JobExecution;

class JobExecutionFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $executionFactory = new JobExecutionFactory(new UniqidJobExecutionIdGenerator());

        $executionWithoutConfig = $executionFactory->create('export');
        self::assertSame('export', $executionWithoutConfig->getJobName());
        self::assertSame(
            ['_id' => $executionWithoutConfig->getId()],
            iterator_to_array($executionWithoutConfig->getParameters())
        );
        $this->assertExecutionIsEmpty($executionWithoutConfig);

        $executionWithId = $executionFactory->create('export', ['_id' => 'idFromOutside']);
        self::assertSame('export', $executionWithId->getJobName());
        self::assertSame('idFromOutside', $executionWithId->getId());
        self::assertSame(
            ['_id' => 'idFromOutside'],
            iterator_to_array($executionWithId->getParameters())
        );
        $this->assertExecutionIsEmpty($executionWithId);

        $executionWithConfig = $executionFactory->create('export', ['string' => 'foo']);
        self::assertSame('export', $executionWithConfig->getJobName());
        self::assertSame(
            ['string' => 'foo', '_id' => $executionWithConfig->getId()],
            iterator_to_array($executionWithConfig->getParameters())
        );
        $this->assertExecutionIsEmpty($executionWithConfig);
    }

    private function assertExecutionIsEmpty(JobExecution $jobExecution): void
    {
        self::assertSame([], iterator_to_array($jobExecution->getSummary()));
        self::assertSame([], $jobExecution->getChildExecutions());
        self::assertNull($jobExecution->getStartTime());
        self::assertNull($jobExecution->getEndTime());
    }
}
