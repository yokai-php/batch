<?php

namespace Yokai\Batch\Tests\Unit\Job\Item\Reader;

use PHPUnit\Framework\TestCase;
use Yokai\Batch\Job\Item\Reader\StaticIterableReader;

class StaticIterableReaderTest extends TestCase
{
    /**
     * @dataProvider items
     */
    public function testRead(iterable $items, array $expected): void
    {
        $reader = new StaticIterableReader($items);

        $actual = [];
        foreach ($reader->read() as $item) {
            $actual[] = $item;
        }

        self::assertSame($expected, $actual);
    }

    public function items(): \Iterator
    {
        $items = [1, 2, 3];

        $aggregate = new class($items) implements \IteratorAggregate
        {
            /**
             * @var array
             */
            private $items;

            public function __construct(array $items)
            {
                $this->items = $items;
            }

            public function getIterator(): \Generator
            {
                yield from $this->items;
            }
        };

        yield [$items, $items];
        yield [new \ArrayIterator($items), $items];
        yield [$aggregate, $items];
    }
}
