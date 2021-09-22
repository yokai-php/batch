<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Job\Item;

use PHPUnit\Framework\TestCase;
use Yokai\Batch\Job\Item\ExpandProcessedItem;
use Yokai\Batch\Job\Item\InvalidItemException;
use Yokai\Batch\Job\Item\ItemJob;
use Yokai\Batch\Job\Item\Processor\CallbackProcessor;
use Yokai\Batch\Job\Item\Reader\StaticIterableReader;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Storage\NullJobExecutionStorage;
use Yokai\Batch\Test\Job\Item\Processor\TestDebugProcessor;
use Yokai\Batch\Test\Job\Item\Reader\TestDebugReader;
use Yokai\Batch\Test\Job\Item\Writer\InMemoryWriter;
use Yokai\Batch\Test\Job\Item\Writer\TestDebugWriter;

class ItemJobTest extends TestCase
{
    public function testExecute(): void
    {
        $reader = new TestDebugReader(
            new StaticIterableReader([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12])
        );
        $processor = new TestDebugProcessor(
            new CallbackProcessor(function ($item) {
                if ($item > 9) {
                    throw new InvalidItemException('Item is greater than 9 got {value}', ['{value}' => $item]);
                }

                return $item * 10;
            })
        );

        $debugWriter = new TestDebugWriter($writer = new InMemoryWriter());

        $job = new ItemJob(4, $reader, $processor, $debugWriter, new NullJobExecutionStorage());
        $job->execute(
            $execution = JobExecution::createRoot('123456789', 'export')
        );

        self::assertSame([10, 20, 30, 40, 50, 60, 70, 80, 90], $writer->getItems());
        self::assertSame([[10, 20, 30, 40], [50, 60, 70, 80], [90]], $writer->getBatchItems());

        self::assertSame(12, $execution->getSummary()->get('read'), '12 items were read');
        self::assertSame(9, $execution->getSummary()->get('processed'), '9 items were processed');
        self::assertSame(3, $execution->getSummary()->get('invalid'), '3 items were invalid');
        self::assertSame(9, $execution->getSummary()->get('write'), '9 items were write');

        $warnings = $execution->getWarnings();
        self::assertCount(3, $warnings);
        foreach ([[0, 9, 10], [1, 10, 11], [2, 11, 12]] as [$warningIdx, $itemIdx, $paramValue]) {
            self::assertSame('Item is greater than 9 got {value}', $warnings[$warningIdx]->getMessage());
            self::assertSame(['{value}' => $paramValue], $warnings[$warningIdx]->getParameters());
            self::assertSame(['itemIndex' => $itemIdx], $warnings[$warningIdx]->getContext());
        }

        $reader->assertWasConfigured();
        $reader->assertWasUsed();

        $processor->assertWasConfigured();
        $processor->assertWasUsed();

        $debugWriter->assertWasConfigured();
        $debugWriter->assertWasUsed();
    }

    public function testWithExpandItem(): void
    {
        $job = new ItemJob(
            4,
            new StaticIterableReader(['eggplant', 'tomato', 'avocado']),
            new CallbackProcessor(fn($item) => new ExpandProcessedItem(['fruit:' . $item, 'vegetable:' . $item])),
            $writer = new InMemoryWriter(),
            new NullJobExecutionStorage()
        );

        $job->execute($execution = JobExecution::createRoot('123456', 'testing'));

        self::assertSame(
            [
                'fruit:eggplant',
                'vegetable:eggplant',
                'fruit:tomato',
                'vegetable:tomato',
                'fruit:avocado',
                'vegetable:avocado',
            ],
            $writer->getItems()
        );
        self::assertSame(3, $execution->getSummary()->get('read'));
        self::assertSame(3, $execution->getSummary()->get('processed'));
        self::assertSame(6, $execution->getSummary()->get('write'));
    }
}
