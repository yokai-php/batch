<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Job\Item\Processor;

use PHPUnit\Framework\TestCase;
use Yokai\Batch\Exception\UnexpectedValueException;
use Yokai\Batch\Job\Item\Processor\ArrayMapProcessor;

class ArrayMapProcessorTest extends TestCase
{
    public function test(): void
    {
        $processor = new ArrayMapProcessor(fn($string) => \mb_strtoupper($string));
        self::assertSame(
            ['firstName' => 'JOHN', 'lastName' => 'DOE'],
            $processor->process(['firstName' => 'John', 'lastName' => 'Doe'])
        );
        self::assertSame(
            ['TOMATO', 'BANANA', 'EGGPLANT'],
            $processor->process(['tomato', 'BaNaNa', 'Eggplant'])
        );
    }

    public function testNotArray(): void
    {
        $this->expectException(UnexpectedValueException::class);
        (new ArrayMapProcessor(fn() => null))->process('anything but array');
    }
}
