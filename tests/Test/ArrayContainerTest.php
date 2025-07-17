<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Test;

use PHPUnit\Framework\TestCase;
use Psr\Container\NotFoundExceptionInterface;
use Yokai\Batch\Test\ArrayContainer;

class ArrayContainerTest extends TestCase
{
    public function testGet(): void
    {
        $container = new ArrayContainer(['foo' => 'FOO', 'bar' => 'BAR']);

        self::assertSame('FOO', $container->get('foo'));
        self::assertSame('BAR', $container->get('bar'));
    }

    public function testGetNotFound(): void
    {
        $container = new ArrayContainer(['foo' => 'FOO', 'bar' => 'BAR']);

        self::expectException(NotFoundExceptionInterface::class);
        $container->get('baz');
    }

    public function testHas(): void
    {
        $container = new ArrayContainer(['foo' => 'FOO', 'bar' => 'BAR']);

        self::assertSame(true, $container->has('foo'));
        self::assertSame(true, $container->has('bar'));
        self::assertSame(false, $container->has('baz'));
    }
}
