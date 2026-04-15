<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Event;

use PHPUnit\Framework\TestCase;
use Yokai\Batch\Event\PostExecuteEvent;
use Yokai\Batch\JobExecution;

final class PostExecuteEventTest extends TestCase
{
    public function test(): void
    {
        $event = new PostExecuteEvent($execution = JobExecution::createRoot('123', 'testing'));
        self::assertSame($execution, $event->getExecution());
    }
}
