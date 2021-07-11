<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Job\Item\Processor;

use Yokai\Batch\Job\Item\Processor\CallbackProcessor;
use PHPUnit\Framework\TestCase;

class CallbackProcessorTest extends TestCase
{
    public function testProcess(): void
    {
        $processor = new CallbackProcessor(fn($item) => \mb_strtolower($item));

        self::assertSame('john', $processor->process('John'));
        self::assertSame('doe', $processor->process('DOE'));
    }
}
