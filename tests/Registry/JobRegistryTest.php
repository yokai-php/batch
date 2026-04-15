<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Registry;

use PHPUnit\Framework\TestCase;
use Yokai\Batch\Exception\UndefinedJobException;
use Yokai\Batch\Job\JobInterface;
use Yokai\Batch\Registry\JobRegistry;

final class JobRegistryTest extends TestCase
{
    public function testRegistry(): void
    {
        $export = $this->createStub(JobInterface::class);
        $import = $this->createStub(JobInterface::class);

        $registry = JobRegistry::fromJobArray(['export' => $export, 'import' => $import]);
        self::assertSame($export, $registry->get('export'));
        self::assertSame($import, $registry->get('import'));
    }

    public function testGetNotFound(): void
    {
        $this->expectException(UndefinedJobException::class);

        $registry = JobRegistry::fromJobArray([]);
        $registry->get('undefinedJob');
    }
}
