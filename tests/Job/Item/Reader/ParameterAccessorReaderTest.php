<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Job\Item\Reader;

use Generator;
use PHPUnit\Framework\TestCase;
use Yokai\Batch\Job\Item\Reader\ParameterAccessorReader;
use Yokai\Batch\Job\Parameters\JobParameterAccessorInterface;
use Yokai\Batch\Job\Parameters\StaticValueParameterAccessor;
use Yokai\Batch\JobExecution;

class ParameterAccessorReaderTest extends TestCase
{
    /**
     * @dataProvider provider
     */
    public function test(JobParameterAccessorInterface $accessor, array $expected): void
    {
        $reader = new ParameterAccessorReader($accessor);
        $reader->setJobExecution(JobExecution::createRoot('123456', 'testing'));

        $actual = [];
        foreach ($reader->read() as $idx => $item) {
            $actual[$idx] = $item;
        }

        self::assertSame($expected, $actual);
    }

    public function provider(): Generator
    {
        yield 'Read from preserved iterable' => [
            new StaticValueParameterAccessor([1 => 'One', 2 => 'Two', 3 => 'Three']),
            [1 => 'One', 2 => 'Two', 3 => 'Three'],
        ];

        yield 'Read from static' => [
            new StaticValueParameterAccessor('Not iterable and converted to array'),
            ['Not iterable and converted to array'],
        ];
    }
}
