<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Test\Launcher;

use PHPUnit\Framework\TestCase;
use Yokai\Batch\Test\Factory\SequenceJobExecutionIdGenerator;
use Yokai\Batch\Test\Launcher\BufferingJobLauncher;

class BufferingJobLauncherTest extends TestCase
{
    public function test(): void
    {
        $launcher = new BufferingJobLauncher(new SequenceJobExecutionIdGenerator(['123', '456', '789']));
        self::assertSame([], $launcher->getExecutions());

        $launcher->launch('testing.foo');
        self::assertCount(1, $launcher->getExecutions());
        self::assertSame('testing.foo', $launcher->getExecutions()[0]->getJobName());
        self::assertSame('123', $launcher->getExecutions()[0]->getId());
        self::assertSame(
            ['_id' => '123'],
            $launcher->getExecutions()[0]->getParameters()->all()
        );

        $launcher->launch('testing.foo');
        $launcher->launch('testing.bar', ['var' => 'value']);
        self::assertCount(3, $launcher->getExecutions());
        self::assertSame('testing.foo', $launcher->getExecutions()[1]->getJobName());
        self::assertSame('456', $launcher->getExecutions()[1]->getId());
        self::assertSame(
            ['_id' => '456'],
            $launcher->getExecutions()[1]->getParameters()->all()
        );
        self::assertSame('testing.bar', $launcher->getExecutions()[2]->getJobName());
        self::assertSame('789', $launcher->getExecutions()[2]->getId());
        self::assertSame(
            ['var' => 'value', '_id' => '789'],
            $launcher->getExecutions()[2]->getParameters()->all()
        );
    }
}
