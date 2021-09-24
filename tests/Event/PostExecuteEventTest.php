<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Event;

use Yokai\Batch\Event\PostExecuteEvent;
use PHPUnit\Framework\TestCase;
use Yokai\Batch\JobExecution;

class PostExecuteEventTest extends TestCase
{
    public function test(): void
    {
        $event = new PostExecuteEvent($execution = JobExecution::createRoot('123', 'testing'));
        self::assertSame($execution, $event->getExecution());
    }
}
