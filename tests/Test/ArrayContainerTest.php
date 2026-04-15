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

        self::assertTrue($container->has('foo'));
        self::assertTrue($container->has('bar'));
        self::assertFalse($container->has('baz'));
    }
}
