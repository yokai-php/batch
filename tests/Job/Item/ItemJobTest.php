<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Job\Item;

use Generator;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Yokai\Batch\Job\Item\ExpandProcessedItem;
use Yokai\Batch\Job\Item\FlushableInterface;
use Yokai\Batch\Job\Item\InitializableInterface;
use Yokai\Batch\Job\Item\InvalidItemException;
use Yokai\Batch\Job\Item\ItemJob;
use Yokai\Batch\Job\Item\ItemProcessorInterface;
use Yokai\Batch\Job\Item\ItemReaderInterface;
use Yokai\Batch\Job\Item\ItemWriterInterface;
use Yokai\Batch\Job\Item\Processor\CallbackProcessor;
use Yokai\Batch\Job\Item\Reader\StaticIterableReader;
use Yokai\Batch\Job\JobExecutionAwareInterface;
use Yokai\Batch\Job\JobParametersAwareInterface;
use Yokai\Batch\Job\SummaryAwareInterface;
use Yokai\Batch\JobExecution;
use Yokai\Batch\JobParameters;
use Yokai\Batch\Storage\NullJobExecutionStorage;
use Yokai\Batch\Summary;
use Yokai\Batch\Test\Job\Item\Writer\InMemoryWriter;
use Yokai\Batch\Tests\Util;

class ItemJobTest extends TestCase
{
    use ProphecyTrait;

    public function testExecute(): void
    {
        $jobExecution = JobExecution::createRoot('123456789', 'export');

        $log = '';

        /** @var ObjectProphecy|ItemReaderInterface $reader */
        $reader = $this->prophesize(ItemReaderInterface::class);
        $this->configureItemElement($reader, 'reader', $log);
        $reader->read()
            ->shouldBeCalledTimes(1)
            ->will(function (): Generator {
                yield from [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
            });

        /** @var ObjectProphecy|ItemProcessorInterface $processor */
        $processor = $this->prophesize(ItemProcessorInterface::class);
        $this->configureItemElement($processor, 'processor', $log);
        $processor->process(Argument::type('int'))
            ->shouldBeCalledTimes(12)
            ->will(function (array $args): int {
                if ($args[0] > 9) {
                    throw new InvalidItemException('Item is greater than 9 got {value}', ['{value}' => $args[0]]);
                }

                return $args[0] * 10;
            });

        /** @var ObjectProphecy|ItemWriterInterface $writer */
        $writer = $this->prophesize(ItemWriterInterface::class);
        $this->configureItemElement($writer, 'writer', $log);
        $writer->write([10, 20, 30, 40])
            ->shouldBeCalledTimes(1);
        $writer->write([50, 60, 70, 80])
            ->shouldBeCalledTimes(1);
        $writer->write([90])
            ->shouldBeCalledTimes(1);

        $job = new ItemJob(
            4,
            $reader->reveal(),
            $processor->reveal(),
            $writer->reveal(),
            new NullJobExecutionStorage()
        );

        $job->execute($jobExecution);

        self::assertSame(12, $jobExecution->getSummary()->get('read'), '12 items were read');
        self::assertSame(9, $jobExecution->getSummary()->get('processed'), '9 items were processed');
        self::assertSame(3, $jobExecution->getSummary()->get('invalid'), '3 items were invalid');
        self::assertSame(9, $jobExecution->getSummary()->get('write'), '9 items were write');

        $expectedLogs = <<<LOGS
reader::setJobExecution
reader::setJobParameters
reader::setSummary
reader::initialize
processor::setJobExecution
processor::setJobParameters
processor::setSummary
processor::initialize
writer::setJobExecution
writer::setJobParameters
writer::setSummary
writer::initialize
reader::flush
processor::flush
writer::flush

LOGS;

        self::assertEquals($expectedLogs, $log);
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

    private function configureItemElement(ObjectProphecy $element, string $role, string &$log): void
    {
        /** @var ObjectProphecy|JobExecutionAwareInterface|JobParametersAwareInterface|SummaryAwareInterface|InitializableInterface|FlushableInterface $element */
        $element->willImplement(JobExecutionAwareInterface::class);
        $element->willImplement(JobParametersAwareInterface::class);
        $element->willImplement(SummaryAwareInterface::class);
        $element->willImplement(InitializableInterface::class);
        $element->willImplement(FlushableInterface::class);
        $element->setJobExecution(Argument::type(JobExecution::class))
            ->shouldBeCalled()
            ->will(Util::createVarLogger($role . '::setJobExecution', $log));
        $element->setJobParameters(Argument::type(JobParameters::class))
            ->shouldBeCalled()
            ->will(Util::createVarLogger($role . '::setJobParameters', $log));
        $element->setSummary(Argument::type(Summary::class))
            ->shouldBeCalled()
            ->will(Util::createVarLogger($role . '::setSummary', $log));
        $element->initialize()
            ->shouldBeCalled()
            ->will(Util::createVarLogger($role . '::initialize', $log));
        $element->flush()
            ->shouldBeCalled()
            ->will(Util::createVarLogger($role . '::flush', $log));
    }
}
