<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Registry;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Yokai\Batch\Exception\UndefinedJobException;
use Yokai\Batch\Job\JobInterface;
use Yokai\Batch\Registry\JobRegistry;

class JobRegistryTest extends TestCase
{
    use ProphecyTrait;

    public function testRegistry()
    {
        /** @var ObjectProphecy|JobInterface $export */
        $export = $this->prophesize(JobInterface::class);
        /** @var ObjectProphecy|JobInterface $import */
        $import = $this->prophesize(JobInterface::class);

        /** @var ContainerInterface|ObjectProphecy $container */
        $container = $this->prophesize(ContainerInterface::class);
        $container->has('export')->willReturn(true);
        $container->get('export')->willReturn($export->reveal());
        $container->has('import')->willReturn(true);
        $container->get('import')->willReturn($import->reveal());

        $registry = new JobRegistry($container->reveal());
        self::assertSame($export->reveal(), $registry->get('export'));
        self::assertSame($import->reveal(), $registry->get('import'));
    }

    public function testGetNotFound()
    {
        $this->expectException(UndefinedJobException::class);

        /** @var ContainerInterface|ObjectProphecy $container */
        $container = $this->prophesize(ContainerInterface::class);
        $container->has(Argument::any())->willReturn(false);
        $container->get(Argument::any())->shouldNotBeCalled();

        $registry = new JobRegistry($container->reveal());
        $registry->get('undefinedJob');
    }
}
