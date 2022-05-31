<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Job\Item\Writer;

use Yokai\Batch\Job\Item\Writer\LaunchJobForItemsBatchWriter;
use PHPUnit\Framework\TestCase;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Test\Factory\SequenceJobExecutionIdGenerator;
use Yokai\Batch\Test\Launcher\BufferingJobLauncher;

class LaunchJobForItemsBatchWriterTest extends TestCase
{
    public function testStaticParameter(): void
    {
        $writer = new LaunchJobForItemsBatchWriter(
            $launcher = new BufferingJobLauncher(new SequenceJobExecutionIdGenerator(['456', '789'])),
            'test.launched_job',
            'itemsInLaunchedJob'
        );

        $writer->setJobExecution($execution = JobExecution::createRoot('123', 'test.launch_for_items_writer'));
        $writer->write([1, 2]);
        $writer->write([3, 4]);

        $executions = $launcher->getExecutions();
        self::assertCount(2, $executions);
        self::assertSame('456', $executions[0]->getId());
        self::assertSame('test.launched_job', $executions[0]->getJobName());
        self::assertSame([1, 2], $executions[0]->getParameters()->get('itemsInLaunchedJob'));
        self::assertSame('789', $executions[1]->getId());
        self::assertSame('test.launched_job', $executions[1]->getJobName());
        self::assertSame([3, 4], $executions[1]->getParameters()->get('itemsInLaunchedJob'));
        self::assertStringContainsString('Triggered job for items batch.', (string)$execution->getLogs());
    }

    public function testClosureParameter(): void
    {
        $writer = new LaunchJobForItemsBatchWriter(
            $launcher = new BufferingJobLauncher(new SequenceJobExecutionIdGenerator(['456', '789'])),
            'test.launched_job',
            fn (iterable $items) => ['itemsInLaunchedJob' => $items, 'extraParameter' => 'foo']
        );

        $writer->setJobExecution($execution = JobExecution::createRoot('123', 'test.launch_for_items_writer'));
        $writer->write([1, 2]);
        $writer->write([3, 4]);

        $executions = $launcher->getExecutions();
        self::assertCount(2, $executions);
        self::assertSame('456', $executions[0]->getId());
        self::assertSame('test.launched_job', $executions[0]->getJobName());
        self::assertSame([1, 2], $executions[0]->getParameters()->get('itemsInLaunchedJob'));
        self::assertSame('foo', $executions[0]->getParameters()->get('extraParameter'));
        self::assertSame('789', $executions[1]->getId());
        self::assertSame('test.launched_job', $executions[1]->getJobName());
        self::assertSame([3, 4], $executions[1]->getParameters()->get('itemsInLaunchedJob'));
        self::assertSame('foo', $executions[1]->getParameters()->get('extraParameter'));
        self::assertStringContainsString('Triggered job for items batch.', (string)$execution->getLogs());
    }
}
