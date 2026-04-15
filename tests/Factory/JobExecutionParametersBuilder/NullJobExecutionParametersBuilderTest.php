<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Factory\JobExecutionParametersBuilder;

use PHPUnit\Framework\TestCase;
use Yokai\Batch\Factory\JobExecutionParametersBuilder\NullJobExecutionParametersBuilder;

final class NullJobExecutionParametersBuilderTest extends TestCase
{
    public function test(): void
    {
        $builder = new NullJobExecutionParametersBuilder();
        self::assertSame(
            [],
            $builder->build('job.foo'),
        );
        self::assertSame(
            [],
            $builder->build('job.baz'),
        );
    }
}
