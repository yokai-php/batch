<?php

namespace Yokai\Batch\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Yokai\Batch\BatchStatus;
use Yokai\Batch\Failure;
use Yokai\Batch\JobExecution;
use Yokai\Batch\JobParameters;
use Yokai\Batch\Summary;
use Yokai\Batch\Warning;

class JobExecutionTest extends TestCase
{
    public function testConstruct(): void
    {
        $fullJobExecution = JobExecution::createChild(
            $parent = JobExecution::createRoot('123456789', 'parent'),
            'export',
            $status = new BatchStatus(BatchStatus::STOPPED),
            $parameters = new JobParameters(),
            $summary = new Summary()
        );
        $fullJobExecution->setStartTime($startTime = new \DateTimeImmutable());
        $fullJobExecution->setEndTime($endTime = new \DateTime());
        $minimalJobExecution = JobExecution::createRoot('987654321', 'import');

        self::assertSame($parent, $parent->getRootExecution());

        self::assertSame($parent, $fullJobExecution->getParentExecution());
        self::assertSame($parent, $fullJobExecution->getRootExecution());
        self::assertSame('123456789', $fullJobExecution->getId());
        self::assertSame('export', $fullJobExecution->getJobName());
        self::assertSame($status, $fullJobExecution->getStatus());
        self::assertSame($parameters, $fullJobExecution->getParameters());
        self::assertSame($summary, $fullJobExecution->getSummary());
        self::assertSame($startTime, $fullJobExecution->getStartTime());
        self::assertSame($endTime, $fullJobExecution->getEndTime());

        self::assertSame($minimalJobExecution, $minimalJobExecution->getRootExecution());
        self::assertSame('987654321', $minimalJobExecution->getId());
        self::assertSame('import', $minimalJobExecution->getJobName());
        self::assertSame(BatchStatus::PENDING, $minimalJobExecution->getStatus()->getValue());
        self::assertNotSame($parameters, $minimalJobExecution->getParameters());
        self::assertInstanceOf(JobParameters::class, $minimalJobExecution->getParameters());
        self::assertNotSame($summary, $minimalJobExecution->getSummary());
        self::assertInstanceOf(Summary::class, $minimalJobExecution->getSummary());
        self::assertNotSame($startTime, $minimalJobExecution->getStartTime());
        self::assertNull($minimalJobExecution->getStartTime());
        self::assertNotSame($endTime, $minimalJobExecution->getEndTime());
        self::assertNull($minimalJobExecution->getEndTime());
    }

    public function testGetParameter()
    {
        $jobExecution = JobExecution::createRoot(
            '123456789',
            'export',
            null,
            new JobParameters(
                ['null' => null, 'string' => 'foo', 'array' => [], 'bool' => false, 'int' => 0, 'float' => 0.000]
            )
        );

        self::assertSame(null, $jobExecution->getParameter('null'));
        self::assertSame('foo', $jobExecution->getParameter('string'));
        self::assertSame([], $jobExecution->getParameter('array'));
        self::assertSame(false, $jobExecution->getParameter('bool'));
        self::assertSame(0, $jobExecution->getParameter('int'));
        self::assertSame(0.000, $jobExecution->getParameter('float'));
    }

    /**
     * @expectedException \Yokai\Batch\Exception\UndefinedJobParameterException
     */
    public function testGetUndefinedParameter()
    {
        $jobExecution = JobExecution::createRoot(
            '123456789',
            'export',
            null,
            new JobParameters(
                ['null' => null, 'string' => 'foo', 'array' => [], 'bool' => false, 'int' => 0, 'float' => 0.000]
            )
        );

        $jobExecution->getParameter('notset');
    }

    public function testInitStartTime(): void
    {
        $jobExecution = JobExecution::createRoot('123456789', 'export');
        self::assertNull($jobExecution->getStartTime());
        $jobExecution->setStartTime($time = new \DateTime());
        self::assertSame($time, $jobExecution->getStartTime());
    }

    /**
     * @expectedException \Yokai\Batch\Exception\ImmutablePropertyException
     */
    public function testStartTimeIsImmutable(): void
    {
        $jobExecution = JobExecution::createRoot('123456789', 'export');
        $jobExecution->setStartTime(new \DateTime());
        $jobExecution->setStartTime(new \DateTime());
    }

    public function testInitEndTime(): void
    {
        $jobExecution = JobExecution::createRoot('123456789', 'export');
        self::assertNull($jobExecution->getEndTime());
        $jobExecution->setEndTime($time = new \DateTime());
        self::assertSame($time, $jobExecution->getEndTime());
    }

    /**
     * @expectedException \Yokai\Batch\Exception\ImmutablePropertyException
     */
    public function testEndTimeIsImmutable(): void
    {
        $jobExecution = JobExecution::createRoot('123456789', 'export');
        $jobExecution->setEndTime(new \DateTime());
        $jobExecution->setEndTime(new \DateTime());
    }

    public function testChangeStatus(): void
    {
        $jobExecution = JobExecution::createRoot('123456789', 'export');
        self::assertTrue($jobExecution->getStatus()->is(BatchStatus::PENDING));
        $jobExecution->setStatus(BatchStatus::COMPLETED);
        self::assertTrue($jobExecution->getStatus()->is(BatchStatus::COMPLETED));
    }

    public function testManipulatesFailures()
    {
        $failureMessage = function (Failure $failure): string {
            return $failure->getMessage();
        };
        $failureToString = function (Failure $failure): string {
            return (string)$failure;
        };

        $jobExecution = JobExecution::createRoot('123456789', 'export');
        self::assertSame([], array_map($failureMessage, $jobExecution->getFailures()));
        self::assertSame([], array_map($failureMessage, $jobExecution->getAllFailures()));
        $jobExecution->addFailureException(new \Exception('Job Failure'));
        self::assertSame(['Job Failure'], array_map($failureMessage, $jobExecution->getFailures()));
        self::assertSame(['Job Failure'], array_map($failureMessage, $jobExecution->getAllFailures()));

        $jobExecution->addChildExecution($prepareJobExecution = $jobExecution->createChildExecution('prepare'));
        $jobExecution->addChildExecution($exportJobExecution = $jobExecution->createChildExecution('export'));
        $prepareJobExecution->addFailureException(new \Exception('Prepare Job Failure'));
        $exportJobExecution->addFailureException(new \Exception('Export Job Failure'));

        self::assertSame(['Job Failure'], array_map($failureMessage, $jobExecution->getFailures()));
        self::assertSame(
            ['Job Failure', 'Prepare Job Failure', 'Export Job Failure'],
            array_map($failureMessage, $jobExecution->getAllFailures())
        );
        self::assertSame(
            ['Job Failure', 'Prepare Job Failure', 'Export Job Failure'],
            array_map($failureToString, $jobExecution->getAllFailures())
        );
    }

    public function testManipulatesWarnings()
    {
        $warningMessage = function (Warning $warning): string {
            return $warning->getMessage();
        };
        $warningToString = function (Warning $warning): string {
            return (string)$warning;
        };

        $jobExecution = JobExecution::createRoot('123456789', 'export');
        self::assertSame([], array_map($warningMessage, $jobExecution->getWarnings()));
        self::assertSame([], array_map($warningMessage, $jobExecution->getAllWarnings()));
        $jobExecution->addWarning(new Warning('Job Warning'));
        self::assertSame(['Job Warning'], array_map($warningMessage, $jobExecution->getWarnings()));
        self::assertSame(['Job Warning'], array_map($warningMessage, $jobExecution->getAllWarnings()));

        $jobExecution->addChildExecution($prepareChildExecution = $jobExecution->createChildExecution('prepare'));
        $jobExecution->addChildExecution($exportChildExecution = $jobExecution->createChildExecution('export'));
        $prepareChildExecution->addWarning(new Warning('Prepare Job Warning'));
        $exportChildExecution->addWarning(new Warning('Export Job Warning'));

        self::assertSame(['Job Warning'], array_map($warningMessage, $jobExecution->getWarnings()));
        self::assertSame(
            ['Job Warning', 'Prepare Job Warning', 'Export Job Warning'],
            array_map($warningMessage, $jobExecution->getAllWarnings())
        );
        self::assertSame(
            ['Job Warning', 'Prepare Job Warning', 'Export Job Warning'],
            array_map($warningToString, $jobExecution->getAllWarnings())
        );
    }

    public function testComputesDuration()
    {
        $jobExecution = JobExecution::createRoot('123456789', 'export');
        $jobExecution->setStartTime(\DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2019-01-01 10:00:00'));
        $jobExecution->setEndTime(\DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2019-01-01 17:30:15'));

        self::assertSame(
            '07h 30m 15s',
            $jobExecution->getDuration()->format('%Hh %Im %Ss')
        );
    }
}
