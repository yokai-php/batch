<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Event;

use PHPUnit\Framework\TestCase;
use Yokai\Batch\Event\PreExecuteEvent;
use Yokai\Batch\JobExecution;

class PreExecuteEventTest extends TestCase
{
    public function test(): void
    {
        $event = new PreExecuteEvent($execution = JobExecution::createRoot('123', 'testing'));
        self::assertSame($execution, $event->getExecution());
    }
}
