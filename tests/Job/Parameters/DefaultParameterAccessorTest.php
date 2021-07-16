<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Job\Parameters;

use PHPUnit\Framework\TestCase;
use Yokai\Batch\Exception\CannotAccessParameterException;
use Yokai\Batch\Job\Parameters\DefaultParameterAccessor;
use Yokai\Batch\Job\Parameters\JobParameterAccessorInterface;
use Yokai\Batch\Job\Parameters\StaticValueParameterAccessor;
use Yokai\Batch\JobExecution;

class DefaultParameterAccessorTest extends TestCase
{
    public function testDefaultWhenInnerFails(): void
    {
        $inner = $this->createMock(JobParameterAccessorInterface::class);
        $inner->method('get')->willThrowException(new CannotAccessParameterException());
        $accessor = new DefaultParameterAccessor($inner, 'default value');

        self::assertSame('default value', $accessor->get(JobExecution::createRoot('123', 'testing')));
    }

    public function testNoDefaultWhenInnerSucceed(): void
    {
        $accessor = new DefaultParameterAccessor(new StaticValueParameterAccessor('static value'), 'default value');

        self::assertSame('static value', $accessor->get(JobExecution::createRoot('123', 'testing')));
    }
}
