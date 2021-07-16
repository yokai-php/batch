<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Job\Parameters;

use Yokai\Batch\Job\Parameters\StaticValueParameterAccessor;
use PHPUnit\Framework\TestCase;
use Yokai\Batch\JobExecution;

class StaticValueParameterAccessorTest extends TestCase
{
    public function test(): void
    {
        $execution = JobExecution::createRoot('123', 'testing');
        self::assertSame('foo', (new StaticValueParameterAccessor('foo'))->get($execution));
        self::assertSame(142, (new StaticValueParameterAccessor(142))->get($execution));
    }
}
