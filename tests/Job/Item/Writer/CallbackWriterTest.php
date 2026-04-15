<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Job\Item\Writer;

use PHPUnit\Framework\TestCase;
use Yokai\Batch\Job\Item\Writer\CallbackWriter;
use Yokai\Batch\JobExecution;

final class CallbackWriterTest extends TestCase
{
    public function testWrite(): void
    {
        $saveditems = [];
        $writer = new CallbackWriter(
            function (array $items, JobExecution $jobExecution) use (&$saveditems, &$receivedJobExecution) {
                $saveditems = [...$saveditems, ...$items];
                $receivedJobExecution = $jobExecution;
            },
        );

        $writer->setJobExecution(JobExecution::createRoot(id: '123', jobName: 'test'));
        $writer->write([1, 2, 3]);
        $writer->write([4, 5, 6]);

        self::assertSame([1, 2, 3, 4, 5, 6], $saveditems);
        self::assertInstanceOf(JobExecution::class, $receivedJobExecution);
        self::assertSame('123', $receivedJobExecution->getId());
        self::assertSame('test', $receivedJobExecution->getJobName());
    }
}
