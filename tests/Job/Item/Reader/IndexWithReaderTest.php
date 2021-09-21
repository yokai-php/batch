<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Job\Item\Reader;

use ArrayIterator;
use Generator;
use PHPUnit\Framework\TestCase;
use Yokai\Batch\Job\Item\Reader\IndexWithReader;
use Yokai\Batch\Job\Item\Reader\StaticIterableReader;
use Yokai\Batch\JobExecution;

class IndexWithReaderTest extends TestCase
{
    /**
     * @dataProvider provider
     */
    public function test(callable $factory, array $expected): void
    {
        /** @var IndexWithReader $reader */
        $reader = $factory();
        $reader->setJobExecution(JobExecution::createRoot('123456', 'testing'));
        $reader->initialize();

        $actual = [];
        foreach ($reader->read() as $index => $item) {
            $actual[$index] = $item;
        }

        $reader->flush();

        self::assertSame($expected, $actual);
    }

    public function provider(): Generator
    {
        $john = ['name' => 'John', 'location' => 'Washington'];
        $marie = ['name' => 'Marie', 'location' => 'London'];
        yield 'Index with array key' => [
            fn() => IndexWithReader::withArrayKey(
                new StaticIterableReader([$john, $marie]),
                'name'
            ),
            ['John' => $john, 'Marie' => $marie],
        ];

        $john = (object)$john;
        $marie = (object)$marie;
        yield 'Index with object property' => [
            fn() => IndexWithReader::withProperty(
                new StaticIterableReader([$john, $marie]),
                'name'
            ),
            ['John' => $john, 'Marie' => $marie],
        ];

        $three = new ArrayIterator([1, 2, 3]);
        $six = new ArrayIterator([1, 2, 3, 4, 5, 6]);
        yield 'Index with object method' => [
            fn() => IndexWithReader::withGetter(
                new StaticIterableReader([$three, $six]),
                'count'
            ),
            [3 => $three, 6 => $six],
        ];

        yield 'Index with arbitrary closure' => [
            fn() => new IndexWithReader(
                new StaticIterableReader([1, 2, 3]),
                fn(int $value) => $value * $value
            ),
            [1 => 1, 4 => 2, 9 => 3],
        ];
    }
}
