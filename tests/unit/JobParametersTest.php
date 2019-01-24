<?php

namespace Yokai\Batch\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Yokai\Batch\JobParameters;

class JobParametersTest extends TestCase
{
    public function testHas()
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

    public function testGet()
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

    /**
     * @expectedException \Yokai\Batch\Exception\UndefinedJobParameterException
     */
    public function testGetUndefinedParameter()
    {
        $parameters = new JobParameters(
            ['null' => null, 'string' => 'foo', 'array' => [], 'bool' => false, 'int' => 0, 'float' => 0.000]
        );

        $parameters->get('notset');
    }

    public function testCount()
    {
        self::assertCount(0, new JobParameters());
        self::assertCount(2, new JobParameters(['null' => null, 'string' => 'foo']));
    }

    public function testGetIterator()
    {
        self::assertSame([], iterator_to_array(new JobParameters()));
        self::assertSame(
            ['null' => null, 'string' => 'foo'],
            iterator_to_array(new JobParameters(['null' => null, 'string' => 'foo']))
        );
    }
}
