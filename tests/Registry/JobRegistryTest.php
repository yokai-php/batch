<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Registry;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Yokai\Batch\Exception\UndefinedJobException;
use Yokai\Batch\Job\JobInterface;
use Yokai\Batch\Registry\JobRegistry;

class JobRegistryTest extends TestCase
{
    use ProphecyTrait;

    public function testRegistry(): void
    {
        /** @var ObjectProphecy|JobInterface $export */
        $export = $this->prophesize(JobInterface::class);
        /** @var ObjectProphecy|JobInterface $import */
        $import = $this->prophesize(JobInterface::class);

        $registry = JobRegistry::fromJobArray(['export' => $export->reveal(), 'import' => $import->reveal()]);
        self::assertSame($export->reveal(), $registry->get('export'));
        self::assertSame($import->reveal(), $registry->get('import'));
    }

    public function testGetNotFound(): void
    {
        $this->expectException(UndefinedJobException::class);

        $registry = JobRegistry::fromJobArray([]);
        $registry->get('undefinedJob');
    }
}
