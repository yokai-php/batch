<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Test\Finder;

use PHPUnit\Framework\TestCase;
use Yokai\Batch\Test\Finder\DummyFinder;

class DummyFinderTest extends TestCase
{
    public function test(): void
    {
        $dummy = new class {
        };
        $finder = new DummyFinder($dummy);

        self::assertSame($dummy, $finder->find('anything'));
        self::assertSame($dummy, $finder->find(1));
        self::assertSame($dummy, $finder->find(1.23));
    }
}
