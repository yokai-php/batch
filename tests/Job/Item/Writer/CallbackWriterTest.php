<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Job\Item\Writer;

use PHPUnit\Framework\TestCase;
use Yokai\Batch\Job\Item\Writer\CallbackWriter;

class CallbackWriterTest extends TestCase
{
    public function testWrite(): void
    {
        $saveditems = [];
        $writer = new CallbackWriter(function (array $items) use (&$saveditems) {
            $saveditems = [...$saveditems, ...$items];
        });
        $writer->write([1, 2, 3]);
        $writer->write([4, 5, 6]);

        self::assertSame([1, 2, 3, 4, 5, 6], $saveditems);
    }
}
