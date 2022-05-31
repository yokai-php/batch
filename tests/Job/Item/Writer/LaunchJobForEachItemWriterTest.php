<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Job\Item\Writer;

use Yokai\Batch\Job\Item\Writer\LaunchJobForEachItemWriter;
use PHPUnit\Framework\TestCase;
use Yokai\Batch\Job\Item\Writer\LaunchJobForItemsBatchWriter;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Test\Factory\SequenceJobExecutionIdGenerator;
use Yokai\Batch\Test\Launcher\BufferingJobLauncher;

class LaunchJobForEachItemWriterTest extends TestCase
{
    public function testStaticParameter(): void
    {
        $writer = new LaunchJobForEachItemWriter(
            $launcher = new BufferingJobLauncher(new SequenceJobExecutionIdGenerator(['abc', 'def', 'hij', 'klm'])),
            'test.launched_job',
            'itemInLaunchedJob'
        );

        $writer->setJobExecution($execution = JobExecution::createRoot('123', 'test.launch_for_items_writer'));
        $writer->write([1, 2]);
        $writer->write([3, 4]);

        $executions = $launcher->getExecutions();
        self::assertCount(4, $executions);
        self::assertSame('abc', $executions[0]->getId());
        self::assertSame('test.launched_job', $executions[0]->getJobName());
        self::assertSame(1, $executions[0]->getParameters()->get('itemInLaunchedJob'));
        self::assertSame('def', $executions[1]->getId());
        self::assertSame('test.launched_job', $executions[1]->getJobName());
        self::assertSame(2, $executions[1]->getParameters()->get('itemInLaunchedJob'));
        self::assertSame('hij', $executions[2]->getId());
        self::assertSame('test.launched_job', $executions[2]->getJobName());
        self::assertSame(3, $executions[2]->getParameters()->get('itemInLaunchedJob'));
        self::assertSame('klm', $executions[3]->getId());
        self::assertSame('test.launched_job', $executions[3]->getJobName());
        self::assertSame(4, $executions[3]->getParameters()->get('itemInLaunchedJob'));
        self::assertStringContainsString('Triggered job for item.', (string)$execution->getLogs());
    }

    public function testClosureParameter(): void
    {
        $writer = new LaunchJobForEachItemWriter(
            $launcher = new BufferingJobLauncher(new SequenceJobExecutionIdGenerator(['abc', 'def', 'hij', 'klm'])),
            'test.launched_job',
            fn ($item) => ['itemInLaunchedJob' => $item, 'extraParameter' => 'foo']
        );

        $writer->setJobExecution($execution = JobExecution::createRoot('123', 'test.launch_for_items_writer'));
        $writer->write([1, 2]);
        $writer->write([3, 4]);

        $executions = $launcher->getExecutions();
        self::assertCount(4, $executions);
        self::assertSame('abc', $executions[0]->getId());
        self::assertSame('test.launched_job', $executions[0]->getJobName());
        self::assertSame(1, $executions[0]->getParameters()->get('itemInLaunchedJob'));
        self::assertSame('foo', $executions[0]->getParameters()->get('extraParameter'));
        self::assertSame('def', $executions[1]->getId());
        self::assertSame('test.launched_job', $executions[1]->getJobName());
        self::assertSame(2, $executions[1]->getParameters()->get('itemInLaunchedJob'));
        self::assertSame('foo', $executions[1]->getParameters()->get('extraParameter'));
        self::assertSame('hij', $executions[2]->getId());
        self::assertSame('test.launched_job', $executions[2]->getJobName());
        self::assertSame(3, $executions[2]->getParameters()->get('itemInLaunchedJob'));
        self::assertSame('foo', $executions[2]->getParameters()->get('extraParameter'));
        self::assertSame('klm', $executions[3]->getId());
        self::assertSame('test.launched_job', $executions[3]->getJobName());
        self::assertSame(4, $executions[3]->getParameters()->get('itemInLaunchedJob'));
        self::assertSame('foo', $executions[3]->getParameters()->get('extraParameter'));
        self::assertStringContainsString('Triggered job for item.', (string)$execution->getLogs());
    }
}
