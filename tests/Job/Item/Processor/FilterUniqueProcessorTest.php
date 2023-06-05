<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Job\Item\Processor;

use ArrayIterator;
use Generator;
use PHPUnit\Framework\TestCase;
use Yokai\Batch\Job\Item\Exception\SkipItemException;
use Yokai\Batch\Job\Item\Processor\FilterUniqueProcessor;

class FilterUniqueProcessorTest extends TestCase
{
    /**
     * @dataProvider provider
     */
    public function test(callable $factory, array $items, array $expected): void
    {
        /** @var FilterUniqueProcessor $processor */
        $processor = $factory();

        $actual = [];
        foreach ($items as $item) {
            try {
                $actual[] = $processor->process($item);
            } catch (SkipItemException $exception) {
                //the item have be filtered and not won't be added to $actual
            }
        }

        self::assertSame($expected, $actual);
    }

    public function provider(): Generator
    {
        $john = ['name' => 'John', 'location' => 'Washington'];
        $johnFiltered = ['name' => 'John', 'location' => 'New-York'];
        $marie = ['name' => 'Marie', 'location' => 'London'];

        yield 'Filter unique with array key' => [
            fn() => FilterUniqueProcessor::withArrayKey('name'),
            [$john, $johnFiltered, $marie],
            [$john, $marie],
        ];

        $john = (object)$john;
        $johnFiltered = (object)$johnFiltered;
        $marie = (object)$marie;
        yield 'Filter unique with object property' => [
            fn() => FilterUniqueProcessor::withProperty('name'),
            [$john, $johnFiltered, $marie],
            [$john, $marie],
        ];

        $three = new ArrayIterator([1, 2, 3]);
        $threeFiltered = new ArrayIterator([4, 5, 6]);
        $six = new ArrayIterator([1, 2, 3, 4, 5, 6]);
        yield 'Filter unique with object method' => [
            fn() => FilterUniqueProcessor::withGetter('count'),
            [$three, $threeFiltered, $six],
            [$three, $six],
        ];

        yield 'Filter arbitrary closure' => [
            fn() => new FilterUniqueProcessor(fn() => 'always'),
            [1, 2, 3, 4, 5, 6],
            [1],
        ];
    }
}
