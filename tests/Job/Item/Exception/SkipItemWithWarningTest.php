<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Job\Item\Exception;

use PHPUnit\Framework\TestCase;
use Yokai\Batch\Job\Item\Exception\SkipItemWithWarning;
use Yokai\Batch\JobExecution;

class SkipItemWithWarningTest extends TestCase
{
    public function test(): void
    {
        $execution = JobExecution::createRoot('123', 'testing');
        $cause = new SkipItemWithWarning('An arbitrary message...');
        $cause->report($execution, 'itemIndex', 'item');

        self::assertCount(1, $execution->getWarnings());
        self::assertSame('An arbitrary message...', $execution->getWarnings()[0]->getMessage());
        self::assertSame([], $execution->getWarnings()[0]->getParameters());
        self::assertSame(['itemIndex' => 'itemIndex', 'item' => 'item'], $execution->getWarnings()[0]->getContext());
    }
}
