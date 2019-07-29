<?php

namespace Yokai\Batch\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder;
use Yokai\Batch\BatchStatus;
use Yokai\Batch\Bridge\Symfony\Serializer\JobExecutionNormalizer;
use Yokai\Batch\Bridge\Symfony\Serializer\SerializerJobExecutionSerializer;
use Yokai\Batch\Factory\JobExecutionFactory;
use Yokai\Batch\Failure;
use Yokai\Batch\Job\JobInterface;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Launcher\SimpleJobLauncher;
use Yokai\Batch\Registry\JobRegistry;
use Yokai\Batch\Storage\FilesystemJobExecutionStorage;
use Yokai\Batch\Storage\JobExecutionStorageInterface;
use Yokai\Batch\Warning;

abstract class JobTestCase extends TestCase
{
    private const ARTIFACTS_DIR = INTEGRATION_ARTIFACT_DIR;
    protected const STORAGE_DIR = self::ARTIFACTS_DIR . '/storage';
    protected const OUTPUT_DIR = self::ARTIFACTS_DIR . '/output';

    private static $run = false;

    /**
     * @inheritDoc
     */
    public static function setUpBeforeClass()
    {
        if (!self::$run) {
            self::$run = true;

            if (is_dir(self::ARTIFACTS_DIR)) {
                @rmdir(self::ARTIFACTS_DIR);
                @mkdir(self::STORAGE_DIR, 0755, true);
                @mkdir(self::OUTPUT_DIR, 0755, true);
            }
        }
    }

    /**
     * @dataProvider variant
     */
    public function testLaunchJob(JobExecutionStorageInterface $jobExecutionStorage): void
    {
        $job = $this->createJob($jobExecutionStorage);
        $jobName = $this->getJobName();

        $launcher = new SimpleJobLauncher(
            self::createJobRegistry([$jobName => $job]),
            new JobExecutionFactory(),
            $jobExecutionStorage
        );

        $jobExecution = $launcher->launch($jobName);

        $this->assertAgainstExecution($jobExecutionStorage, $jobExecution);
    }

    public function variant(): \Iterator
    {
        foreach ($this->storages() as $storage) {
            yield [$storage];
        }
    }

    protected static function createJobRegistry(array $jobs): JobRegistry
    {
        $container = new class ($jobs) implements ContainerInterface {
            private $jobs;
            public function __construct(array $jobs)
            {
                $this->jobs = $jobs;
            }
            public function get($id)
            {
                return $this->jobs[$id];
            }
            public function has($id)
            {
                return $this->jobs[$id] instanceof JobInterface;
            }
        };

        return new JobRegistry($container);
    }

    abstract protected function createJob(JobExecutionStorageInterface $executionStorage): JobInterface;

    abstract protected function getJobName(): string;

    protected function assertAgainstExecution(
        JobExecutionStorageInterface $jobExecutionStorage,
        JobExecution $jobExecution
    ): void {
        $this->compareExecutions(
            $jobExecution,
            $jobExecutionStorage->retrieve($jobExecution->getJobName(), $jobExecution->getId())
        );
    }

    private function compareExecutions(JobExecution $jobExecution, JobExecution $storedJobExecution)
    {
        self::assertSame($jobExecution->getId(), $storedJobExecution->getId());
        self::assertSame($jobExecution->getJobName(), $storedJobExecution->getJobName());
        self::compareStatuses($jobExecution->getStatus(), $storedJobExecution->getStatus());
        self::assertSame(
            iterator_to_array($jobExecution->getParameters()),
            iterator_to_array($storedJobExecution->getParameters())
        );
        self::assertSame(
            iterator_to_array($jobExecution->getSummary()),
            iterator_to_array($storedJobExecution->getSummary())
        );
        self::compareDates($jobExecution->getStartTime(), $storedJobExecution->getStartTime());
        self::compareDates($jobExecution->getEndTime(), $storedJobExecution->getEndTime());

        self::compareFailures($jobExecution->getFailures(), $storedJobExecution->getFailures());
        self::compareWarnings($jobExecution->getWarnings(), $storedJobExecution->getWarnings());

        foreach ($jobExecution->getChildExecutions() as $childExecution) {
            $this->compareExecutions(
                $childExecution,
                $storedJobExecution->getChildExecution($childExecution->getJobName())
            );
        }
    }

    private function storages(): \Iterator
    {
        $serializer = new Serializer([new JobExecutionNormalizer()], [new Encoder\JsonEncoder()]);

        yield new FilesystemJobExecutionStorage(
            new SerializerJobExecutionSerializer($serializer, 'json'),
            self::STORAGE_DIR,
            'json'
        );
    }

    private static function compareStatuses(BatchStatus $expected, BatchStatus $actual)
    {
        self::assertSame($expected->getValue(), $actual->getValue());
    }

    private static function compareDates(?\DateTimeInterface $expected, ?\DateTimeInterface $actual)
    {
        self::assertSame(
            $expected ? $expected->format(\DateTime::ISO8601) : null,
            $actual ? $actual->format(\DateTime::ISO8601) : null
        );
    }

    /**
     * @param Failure[] $expected
     * @param Failure[] $actual
     */
    private static function compareFailures(array $expected, array $actual)
    {
        self::assertCount(count($expected), $actual);

        foreach ($expected as $idx => $expectedFailure) {
            $actualFailure = $actual[$idx] ?? null;
            self::assertInstanceOf(Failure::class, $actualFailure);
            self::assertSame($expectedFailure->getClass(), $actualFailure->getClass());
            self::assertSame($expectedFailure->getMessage(), $actualFailure->getMessage());
            self::assertSame($expectedFailure->getCode(), $actualFailure->getCode());
            self::assertSame($expectedFailure->getParameters(), $actualFailure->getParameters());
            self::assertSame($expectedFailure->getTrace(), $actualFailure->getTrace());
        }
    }

    /**
     * @param Warning[] $expected
     * @param Warning[] $actual
     */
    private static function compareWarnings(array $expected, array $actual)
    {
        self::assertCount(count($expected), $actual);

        foreach ($expected as $idx => $expectedWarning) {
            $actualWarning = $actual[$idx] ?? null;
            self::assertInstanceOf(Warning::class, $actualWarning);
            self::assertSame($expectedWarning->getMessage(), $actualWarning->getMessage());
            self::assertSame($expectedWarning->getParameters(), $actualWarning->getParameters());
        }
    }
}
