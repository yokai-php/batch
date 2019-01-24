<?php

namespace Yokai\Batch\Tests\Unit\Job\Item\Processor;

use PHPUnit\Framework\TestCase;
use Yokai\Batch\Job\Item\Processor\NullProcessor;

class NullProcessorTest extends TestCase
{
    public function testProcess(): void
    {
        $processor = new NullProcessor();

        $items = ['string', null, 1, 0.999, false, ['array']];
        foreach ($items as $item) {
            self::assertSame($item, $processor->process($item));
        }
    }
}
