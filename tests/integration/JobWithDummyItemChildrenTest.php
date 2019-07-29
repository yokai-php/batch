<?php

namespace Yokai\Batch\Tests\Integration;

use Yokai\Batch\BatchStatus;
use Yokai\Batch\Job\Item\ItemJob;
use Yokai\Batch\Job\Item\ItemWriterInterface;
use Yokai\Batch\Job\Item\Processor\NullProcessor;
use Yokai\Batch\Job\Item\Reader\StaticIterableReader;
use Yokai\Batch\Job\JobInterface;
use Yokai\Batch\Job\JobWithChildJobs;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Storage\JobExecutionStorageInterface;

class JobWithDummyItemChildrenTest extends JobTestCase
{
    private const OUTPUT_FILE = self::OUTPUT_DIR.'/job-with-dummy-item-children.txt';

    protected function createJob(JobExecutionStorageInterface $executionStorage): JobInterface
    {
        $output = self::OUTPUT_FILE;

        $fileLineWriter = new class($output) implements ItemWriterInterface
        {
            /**
             * @var string
             */
            private $file;

            public function __construct(string $file)
            {
                $this->file = $file;
            }

            public function write(iterable $items): void
            {
                foreach ($items as $item) {
                    file_put_contents($this->file, $item.PHP_EOL, FILE_APPEND);
                }
            }
        };

        return new JobWithChildJobs(
            $executionStorage,
            self::createJobRegistry(
                [
                    'one-two-three' => new ItemJob(
                        1,
                        new StaticIterableReader([1, 2, 3]),
                        new NullProcessor(),
                        $fileLineWriter,
                        $executionStorage
                    ),
                    'four-five-six' => new ItemJob(
                        2,
                        new StaticIterableReader([4, 5, 6]),
                        new NullProcessor(),
                        $fileLineWriter,
                        $executionStorage
                    ),
                    'seven-height-nine' => new ItemJob(
                        3,
                        new StaticIterableReader([7, 8, 9]),
                        new NullProcessor(),
                        $fileLineWriter,
                        $executionStorage
                    ),
                ]
            ),
            ['one-two-three', 'four-five-six', 'seven-height-nine']
        );
    }

    protected function getJobName(): string
    {
        return 'job-with-dummy-item-children';
    }

    protected function assertAgainstExecution(
        JobExecutionStorageInterface $jobExecutionStorage,
        JobExecution $jobExecution
    ): void {
        parent::assertAgainstExecution($jobExecutionStorage, $jobExecution);

        self::assertSame(BatchStatus::COMPLETED, $jobExecution->getStatus()->getValue());
        foreach ($jobExecution->getChildExecutions() as $childExecution) {
            self::assertSame(BatchStatus::COMPLETED, $childExecution->getStatus()->getValue());
        }

        $output = self::OUTPUT_FILE;
        $expected = <<<OUT
1
2
3
4
5
6
7
8
9

OUT;

        self::assertFileExists($output);
        self::assertEquals($expected, file_get_contents($output));
    }
}
