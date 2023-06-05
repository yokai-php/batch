<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Registry;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\NotFoundExceptionInterface;
use Yokai\Batch\Job\JobInterface;
use Yokai\Batch\Registry\JobContainer;

class JobContainerTest extends TestCase
{
    use ProphecyTrait;

    public function testGet(): void
    {
        $foo = $this->prophesize(JobInterface::class)->reveal();
        $bar = $this->prophesize(JobInterface::class)->reveal();
        $container = new JobContainer(['foo' => $foo, 'bar' => $bar]);
        self::assertSame($foo, $container->get('foo'));
        self::assertSame($bar, $container->get('bar'));
    }

    public function testGetNotFound(): void
    {
        $this->expectExceptionMessage('You have requested a non-existent job "bar".');
        $this->expectException(NotFoundExceptionInterface::class);
        $foo = $this->prophesize(JobInterface::class)->reveal();
        $container = new JobContainer(['foo' => $foo]);
        $container->get('bar');
    }

    public function testHas(): void
    {
        $foo = $this->prophesize(JobInterface::class)->reveal();
        $container = new JobContainer(['foo' => $foo]);
        self::assertTrue($container->has('foo'));
        self::assertFalse($container->has('bar'));
    }
}
