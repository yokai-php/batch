<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Finder;

use PHPUnit\Framework\TestCase;
use Yokai\Batch\Finder\CallbackFinder;

class CallbackFinderTest extends TestCase
{
    public function test(): void
    {
        $integers = new class() {
        };
        $numbers = new class() {
        };
        $default = new class() {
        };
        $finder = new CallbackFinder([
            [fn($subject) => \is_int($subject), $integers],
            [fn($subject) => \is_numeric($subject), $numbers],
        ], $default);

        self::assertSame($integers, $finder->find(1));
        self::assertSame($integers, $finder->find(2003));
        self::assertSame($numbers, $finder->find(20.6));
        self::assertSame($numbers, $finder->find('89'));
        self::assertSame($default, $finder->find('Barney'));
        self::assertSame($default, $finder->find(false));
    }
}
