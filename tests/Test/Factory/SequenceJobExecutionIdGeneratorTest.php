<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Test\Factory;

use Yokai\Batch\Test\Factory\SequenceJobExecutionIdGenerator;
use PHPUnit\Framework\TestCase;

class SequenceJobExecutionIdGeneratorTest extends TestCase
{
    public function test(): void
    {
        $generator = new SequenceJobExecutionIdGenerator(['123', '456', '789']);

        self::assertSame('123', $generator->generate());
        self::assertSame('456', $generator->generate());
        self::assertSame('789', $generator->generate());

        // at this point we are out of sequence bounds
        // generator will keep returning empty string
        self::assertSame('', $generator->generate());
        self::assertSame('', $generator->generate());
    }
}
