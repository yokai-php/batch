<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Job\Item\Exception;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Throwable;
use ValueError;
use Yokai\Batch\Exception\LogicException;
use Yokai\Batch\Job\Item\Exception\SkipItemOnError;
use Yokai\Batch\JobExecution;

class SkipItemOnErrorTest extends TestCase
{
    /**
     * @dataProvider provider
     */
    public function test(Throwable $error): void
    {
        $execution = JobExecution::createRoot('123', 'testing');
        $cause = new SkipItemOnError($error);
        $cause->report($execution, 'itemIndex', 'item');

        self::assertSame(1, $execution->getSummary()->get('errored'));
        self::assertCount(1, $execution->getWarnings());
        self::assertSame('An error occurred.', $execution->getWarnings()[0]->getMessage());
        self::assertSame([], $execution->getWarnings()[0]->getParameters());
        self::assertSame(
            [
                'itemIndex' => 'itemIndex',
                'item' => 'item',
                'class' => \get_class($error),
                'message' => $error->getMessage(),
                'code' => $error->getCode(),
                'trace' => $error->getTraceAsString(),
            ],
            $execution->getWarnings()[0]->getContext()
        );
    }

    public function provider(): \Generator
    {
        yield [new RuntimeException('RuntimeException from SPL')];
        yield [new LogicException('LogicException from library')];
        yield [new ValueError('ValueError from PHP core')];
    }
}
