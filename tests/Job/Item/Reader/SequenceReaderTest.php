<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Job\Item\Reader;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Yokai\Batch\Job\Item\FlushableInterface;
use Yokai\Batch\Job\Item\InitializableInterface;
use Yokai\Batch\Job\Item\ItemReaderInterface;
use Yokai\Batch\Job\Item\Reader\SequenceReader;
use Yokai\Batch\Job\JobExecutionAwareInterface;
use Yokai\Batch\Job\JobParametersAwareInterface;
use Yokai\Batch\Job\SummaryAwareInterface;
use Yokai\Batch\JobExecution;
use Yokai\Batch\JobParameters;
use Yokai\Batch\Summary;
use Yokai\Batch\Tests\Util;

class SequenceReaderTest extends TestCase
{
    use ProphecyTrait;

    public function testRead(): void
    {
        $log = '';

        /** @var ObjectProphecy|ItemReaderInterface|JobExecutionAwareInterface|JobParametersAwareInterface|SummaryAwareInterface|InitializableInterface|FlushableInterface $reader123Array */
        $reader123Array = $this->prophesize(ItemReaderInterface::class);
        $reader123Array->willImplement(JobExecutionAwareInterface::class);
        $reader123Array->willImplement(JobParametersAwareInterface::class);
        $reader123Array->willImplement(SummaryAwareInterface::class);
        $reader123Array->willImplement(InitializableInterface::class);
        $reader123Array->willImplement(FlushableInterface::class);
        $reader123Array->setJobExecution(Argument::type(JobExecution::class))
            ->shouldBeCalled()
            ->will(Util::createVarLogger('setJobExecution', $log));
        $reader123Array->setJobParameters(Argument::type(JobParameters::class))
            ->shouldBeCalled()
            ->will(Util::createVarLogger('setJobParameters', $log));
        $reader123Array->setSummary(Argument::type(Summary::class))
            ->shouldBeCalled()
            ->will(Util::createVarLogger('setSummary', $log));
        $reader123Array->initialize()
            ->shouldBeCalled()
            ->will(Util::createVarLogger('initialize', $log));
        $reader123Array->flush()
            ->shouldBeCalled()
            ->will(Util::createVarLogger('flush', $log));
        $reader123Array->read()
            ->shouldBeCalledTimes(1)
            ->willReturn([1, 2, 3]);

        /** @var ObjectProphecy|ItemReaderInterface $reader456Iterator */
        $reader456Iterator = $this->prophesize(ItemReaderInterface::class);
        $reader456Iterator->read()
            ->shouldBeCalledTimes(1)
            ->willReturn(new \ArrayIterator([4, 5, 6]));

        /** @var ObjectProphecy|ItemReaderInterface $reader789IteratorAggregate */
        $reader789IteratorAggregate = $this->prophesize(ItemReaderInterface::class);
        $reader789IteratorAggregate->read()
            ->shouldBeCalledTimes(1)
            ->willReturn(
                new class implements \IteratorAggregate
                {
                    public function getIterator()
                    {
                        foreach ([7, 8, 9] as $value) {
                            yield $value;
                        }
                    }
                }
            );

        $reader = new SequenceReader(
            [
                $reader123Array->reveal(),
                $reader456Iterator->reveal(),
                $reader789IteratorAggregate->reveal(),
            ]
        );
        $reader->setJobExecution(JobExecution::createRoot('123456789', 'export'));
        $reader->initialize();
        $value = $reader->read();
        $reader->flush();

        self::assertInstanceOf(\Generator::class, $value);
        self::assertSame([1, 2, 3, 4, 5, 6, 7, 8, 9], iterator_to_array($value));

        $expectedLog = <<<LOG
setJobExecution
setJobParameters
setSummary
initialize
flush

LOG;
        self::assertSame($expectedLog, $log);
    }
}
