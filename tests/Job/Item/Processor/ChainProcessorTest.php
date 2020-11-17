<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Job\Item\Processor;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Yokai\Batch\Job\Item\FlushableInterface;
use Yokai\Batch\Job\Item\InitializableInterface;
use Yokai\Batch\Job\Item\ItemProcessorInterface;
use Yokai\Batch\Job\Item\Processor\ChainProcessor;
use Yokai\Batch\Job\JobExecutionAwareInterface;
use Yokai\Batch\Job\JobParametersAwareInterface;
use Yokai\Batch\Job\SummaryAwareInterface;
use Yokai\Batch\JobExecution;
use Yokai\Batch\JobParameters;
use Yokai\Batch\Summary;
use Yokai\Batch\Tests\Util;

class ChainProcessorTest extends TestCase
{
    use ProphecyTrait;

    public function testProcess(): void
    {
        $log = '';

        /** @var ObjectProphecy|ItemProcessorInterface $subtract1Processor */
        $subtract1Processor = $this->prophesize(ItemProcessorInterface::class);
        $subtract1Processor->process(Argument::type('int'))
            ->shouldBeCalled()
            ->will(function (array $args): int {
                return $args[0] - 1;
            });

        /** @var ObjectProphecy|ItemProcessorInterface $multiplyBy2Processor */
        $multiplyBy2Processor = $this->prophesize(ItemProcessorInterface::class);
        $multiplyBy2Processor->process(Argument::type('int'))
            ->shouldBeCalled()
            ->will(function (array $args): int {
                return $args[0] * 2;
            });

        /** @var ObjectProphecy|ItemProcessorInterface|JobExecutionAwareInterface|JobParametersAwareInterface|SummaryAwareInterface|InitializableInterface|FlushableInterface $add10Processor */
        $add10Processor = $this->prophesize(ItemProcessorInterface::class);
        $add10Processor->willImplement(JobExecutionAwareInterface::class);
        $add10Processor->willImplement(JobParametersAwareInterface::class);
        $add10Processor->willImplement(SummaryAwareInterface::class);
        $add10Processor->willImplement(InitializableInterface::class);
        $add10Processor->willImplement(FlushableInterface::class);
        $add10Processor->setJobExecution(Argument::type(JobExecution::class))
            ->shouldBeCalled()
            ->will(Util::createVarLogger('setJobExecution', $log));
        $add10Processor->setJobParameters(Argument::type(JobParameters::class))
            ->shouldBeCalled()
            ->will(Util::createVarLogger('setJobParameters', $log));
        $add10Processor->setSummary(Argument::type(Summary::class))
            ->shouldBeCalled()
            ->will(Util::createVarLogger('setSummary', $log));
        $add10Processor->initialize()
            ->shouldBeCalled()
            ->will(Util::createVarLogger('initialize', $log));
        $add10Processor->flush()
            ->shouldBeCalled()
            ->will(Util::createVarLogger('flush', $log));
        $add10Processor->process(Argument::type('int'))
            ->shouldBeCalled()
            ->will(function (array $args): int {
                return $args[0] + 10;
            });

        $processor = new ChainProcessor(
            [$subtract1Processor->reveal(), $multiplyBy2Processor->reveal(), $add10Processor->reveal()]
        );

        $processor->setJobExecution(JobExecution::createRoot('123456789', 'export'));
        $processor->initialize();
        // formula is (X - 1) * 2 + 10
        self::assertSame(14, $processor->process(3));
        self::assertSame(28, $processor->process(10));
        $processor->flush();

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
