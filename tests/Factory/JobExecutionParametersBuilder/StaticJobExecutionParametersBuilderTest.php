<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Factory\JobExecutionParametersBuilder;

use PHPUnit\Framework\TestCase;
use Yokai\Batch\Factory\JobExecutionParametersBuilder\StaticJobExecutionParametersBuilder;

final class StaticJobExecutionParametersBuilderTest extends TestCase
{
    public function test(): void
    {
        $builder = new StaticJobExecutionParametersBuilder(['string' => 'foo', 'number' => 1, 'bool' => false]);
        self::assertSame(
            ['string' => 'foo', 'number' => 1, 'bool' => false],
            $builder->build('job.foo'),
        );
        self::assertSame(
            ['string' => 'foo', 'number' => 1, 'bool' => false],
            $builder->build('job.baz'),
        );
    }
}
