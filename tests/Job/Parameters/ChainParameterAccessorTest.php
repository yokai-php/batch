<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Job\Parameters;

use PHPUnit\Framework\TestCase;
use Yokai\Batch\Exception\CannotAccessParameterException;
use Yokai\Batch\Job\Parameters\ChainParameterAccessor;
use Yokai\Batch\Job\Parameters\JobParameterAccessorInterface;
use Yokai\Batch\Job\Parameters\StaticValueParameterAccessor;
use Yokai\Batch\JobExecution;

class ChainParameterAccessorTest extends TestCase
{
    public function test(): void
    {
        $firstFailing = $this->createMock(JobParameterAccessorInterface::class);
        $firstFailing->expects($this->once())->method('get')->willThrowException(new CannotAccessParameterException());
        $secondSucceed = new StaticValueParameterAccessor('expected value');
        $thirdNotCalled = $this->createMock(JobParameterAccessorInterface::class);
        $thirdNotCalled->expects($this->never())->method('get')->willReturn('never called');
        $accessor = new ChainParameterAccessor([$firstFailing, $secondSucceed, $thirdNotCalled]);

        self::assertSame('expected value', $accessor->get(JobExecution::createRoot('123', 'testing')));
    }

    public function testAllFailed(): void
    {
        $this->expectException(CannotAccessParameterException::class);

        $firstFailing = $this->createMock(JobParameterAccessorInterface::class);
        $firstFailing->expects($this->once())->method('get')->willThrowException(new CannotAccessParameterException());
        $secondFailing = $this->createMock(JobParameterAccessorInterface::class);
        $secondFailing->expects($this->once())->method('get')->willThrowException(new CannotAccessParameterException());
        $accessor = new ChainParameterAccessor([$firstFailing, $secondFailing]);

        $accessor->get(JobExecution::createRoot('123', 'testing'));
    }
}
