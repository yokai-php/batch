<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests;

use PHPUnit\Framework\TestCase;
use Yokai\Batch\Exception\UndefinedJobParameterException;
use Yokai\Batch\JobParameters;

class JobParametersTest extends TestCase
{
    public function testHas(): void
    {
        $parameters = new JobParameters(
            ['null' => null, 'string' => 'foo', 'array' => [], 'bool' => false, 'int' => 0, 'float' => 0.000]
        );

        self::assertTrue($parameters->has('null'));
        self::assertTrue($parameters->has('string'));
        self::assertTrue($parameters->has('array'));
        self::assertTrue($parameters->has('int'));
        self::assertTrue($parameters->has('float'));

        self::assertFalse($parameters->has('notset'));
    }

    public function testGet(): void
    {
        $parameters = new JobParameters(
            ['null' => null, 'string' => 'foo', 'array' => [], 'bool' => false, 'int' => 0, 'float' => 0.000]
        );

        self::assertSame(null, $parameters->get('null'));
        self::assertSame('foo', $parameters->get('string'));
        self::assertSame([], $parameters->get('array'));
        self::assertSame(false, $parameters->get('bool'));
        self::assertSame(0, $parameters->get('int'));
        self::assertSame(0.000, $parameters->get('float'));
    }

    public function testGetUndefinedParameter(): void
    {
        $this->expectException(UndefinedJobParameterException::class);

        $parameters = new JobParameters(
            ['null' => null, 'string' => 'foo', 'array' => [], 'bool' => false, 'int' => 0, 'float' => 0.000]
        );

        $parameters->get('notset');
    }

    public function testCount(): void
    {
        self::assertCount(0, new JobParameters());
        self::assertCount(2, new JobParameters(['null' => null, 'string' => 'foo']));
    }

    public function testAll(): void
    {
        self::assertSame([], (new JobParameters())->all());
        self::assertSame(
            ['null' => null, 'string' => 'foo'],
            (new JobParameters(['null' => null, 'string' => 'foo']))->all()
        );
    }

    public function testGetIterator(): void
    {
        self::assertSame([], iterator_to_array(new JobParameters()));
        self::assertSame(
            ['null' => null, 'string' => 'foo'],
            iterator_to_array(new JobParameters(['null' => null, 'string' => 'foo']))
        );
    }
}
