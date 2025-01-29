<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Finder;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use PHPUnit\Framework\TestCase;
use Throwable;
use TypeError;
use Yokai\Batch\Finder\ClassMapFinder;

class ClassMapFinderTest extends TestCase
{
    public function test(): void
    {
        $dates = new class {
        };
        $exceptions = new class {
        };
        $default = new class {
        };
        $finder = new ClassMapFinder([DateTimeInterface::class => $dates, Throwable::class => $exceptions], $default);

        self::assertSame($dates, $finder->find(new DateTime()));
        self::assertSame($dates, $finder->find(new DateTimeImmutable()));
        self::assertSame($exceptions, $finder->find(new Exception()));
        self::assertSame($exceptions, $finder->find(new TypeError()));
        self::assertSame($default, $finder->find('string'));
        self::assertSame($default, $finder->find(123));
    }
}
