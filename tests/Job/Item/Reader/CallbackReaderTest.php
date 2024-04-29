<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Job\Item\Reader;

use PHPUnit\Framework\TestCase;
use Yokai\Batch\Job\Item\Reader\CallbackReader;

class CallbackReaderTest extends TestCase
{
    /**
     * @dataProvider provider
     */
    public function test(array $expected, \Closure $closure): void
    {
        $items = [];
        foreach ((new CallbackReader($closure))->read() as $item) {
            $items[] = $item;
        }

        self::assertSame($expected, $items);
    }

    public static function provider(): \Generator
    {
        yield 'array' => [
            [1, 2, 3],
            fn() => [1, 2, 3],
        ];
        yield 'iterator' => [
            [1, 2, 3],
            fn() => new \ArrayIterator([1, 2, 3]),
        ];
        yield 'generator' => [
            [1, 2, 3],
            function () {
                yield 1;
                yield 2;
                yield 3;
            },
        ];
    }
}
