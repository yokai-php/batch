<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Factory\JobExecutionParametersBuilder;

use PHPUnit\Framework\TestCase;
use Yokai\Batch\Factory\JobExecutionParametersBuilder\ChainJobExecutionParametersBuilder;
use Yokai\Batch\Factory\JobExecutionParametersBuilder\PerJobJobExecutionParametersBuilder;
use Yokai\Batch\Factory\JobExecutionParametersBuilder\StaticJobExecutionParametersBuilder;

final class ChainJobExecutionParametersBuilderTest extends TestCase
{
    public function test(): void
    {
        $builder = new ChainJobExecutionParametersBuilder([
            new PerJobJobExecutionParametersBuilder([
                'job.foo' => ['foo' => true],
                'job.bar' => ['bar' => true],
            ]),
            new StaticJobExecutionParametersBuilder(['default' => true]),
        ]);
        self::assertSame(
            ['foo' => true, 'default' => true],
            $builder->build('job.foo'),
        );
        self::assertSame(
            ['bar' => true, 'default' => true],
            $builder->build('job.bar'),
        );
        self::assertSame(
            ['default' => true],
            $builder->build('job.baz'),
        );
    }
}
