<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Factory\JobExecutionParametersBuilder;

use PHPUnit\Framework\TestCase;
use Yokai\Batch\Factory\JobExecutionParametersBuilder\PerJobJobExecutionParametersBuilder;

class PerJobJobExecutionParametersBuilderTest extends TestCase
{
    public function test(): void
    {
        $builder = new PerJobJobExecutionParametersBuilder([
            'job.foo' => ['string' => 'foo', 'number' => 1, 'bool' => false],
            'job.bar' => ['string' => 'bar', 'number' => 2, 'bool' => true],
        ]);
        self::assertSame(
            ['string' => 'foo', 'number' => 1, 'bool' => false],
            $builder->build('job.foo'),
        );
        self::assertSame(
            ['string' => 'bar', 'number' => 2, 'bool' => true],
            $builder->build('job.bar'),
        );
        self::assertSame(
            [],
            $builder->build('job.baz'),
        );
    }
}
