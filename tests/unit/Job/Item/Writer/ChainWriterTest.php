<?php

namespace Yokai\Batch\Tests\Unit\Job\Item\Writer;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Yokai\Batch\Job\Item\FlushableInterface;
use Yokai\Batch\Job\Item\InitializableInterface;
use Yokai\Batch\Job\Item\ItemWriterInterface;
use Yokai\Batch\Job\Item\Writer\ChainWriter;
use Yokai\Batch\Job\JobExecutionAwareInterface;
use Yokai\Batch\Job\JobParametersAwareInterface;
use Yokai\Batch\Job\SummaryAwareInterface;
use Yokai\Batch\JobExecution;
use Yokai\Batch\JobParameters;
use Yokai\Batch\Summary;
use Yokai\Batch\Tests\Unit\Util;

class ChainWriterTest extends TestCase
{
    public function testWrite()
    {
        $items = [1, 2, 3];

        $log = '';

        /** @var ObjectProphecy|ItemWriterInterface|JobExecutionAwareInterface|JobParametersAwareInterface|SummaryAwareInterface|InitializableInterface|FlushableInterface $writer1 */
        $writer1 = $this->prophesize(ItemWriterInterface::class);
        $writer1->willImplement(JobExecutionAwareInterface::class);
        $writer1->willImplement(JobParametersAwareInterface::class);
        $writer1->willImplement(SummaryAwareInterface::class);
        $writer1->willImplement(InitializableInterface::class);
        $writer1->willImplement(FlushableInterface::class);
        $writer1->setJobExecution(Argument::type(JobExecution::class))
            ->shouldBeCalled()
            ->will(Util::createVarLogger('setJobExecution', $log));
        $writer1->setJobParameters(Argument::type(JobParameters::class))
            ->shouldBeCalled()
            ->will(Util::createVarLogger('setJobParameters', $log));
        $writer1->setSummary(Argument::type(Summary::class))
            ->shouldBeCalled()
            ->will(Util::createVarLogger('setSummary', $log));
        $writer1->initialize()
            ->shouldBeCalled()
            ->will(Util::createVarLogger('initialize', $log));
        $writer1->flush()
            ->shouldBeCalled()
            ->will(Util::createVarLogger('flush', $log));
        $writer1->write($items)
            ->shouldBeCalledTimes(1);

        /** @var ObjectProphecy|ItemWriterInterface $writer2 */
        $writer2 = $this->prophesize(ItemWriterInterface::class);
        $writer2->write($items)
            ->shouldBeCalledTimes(1);

        $writer = new ChainWriter(
            [
                $writer1->reveal(),
                $writer2->reveal(),
            ]
        );
        $writer->setJobExecution(JobExecution::createRoot('123456789', 'export'));
        $writer->initialize();
        $writer->write($items);
        $writer->flush();

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
