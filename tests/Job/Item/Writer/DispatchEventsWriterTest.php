<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Job\Item\Writer;

use PHPUnit\Framework\TestCase;
use Yokai\Batch\Event\PostWriteEvent;
use Yokai\Batch\Event\PreWriteEvent;
use Yokai\Batch\Job\Item\Writer\DispatchEventsWriter;
use Yokai\Batch\Job\Item\Writer\NullWriter;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Test\Job\Item\Writer\TestDebugWriter;
use Yokai\Batch\Tests\Dummy\DebugEventDispatcher;

class DispatchEventsWriterTest extends TestCase
{
    public function test(): void
    {
        $writer = new DispatchEventsWriter(
            $dispatcher = new DebugEventDispatcher(),
            $decorated = new TestDebugWriter(new NullWriter()),
        );
        $dispatcher->addListener(PostWriteEvent::class, function () {
            throw new \RuntimeException('Test exception');
        });

        $writer->setJobExecution($execution = JobExecution::createRoot('123', 'foo'));
        $writer->initialize();
        $writer->write(['irrelevant']);
        $writer->flush();

        $decorated->assertWasConfigured();
        $decorated->assertWasUsed();
        $events = $dispatcher->getEvents();
        self::assertCount(2, $events);
        self::assertInstanceOf(PreWriteEvent::class, $events[0] ?? null);
        self::assertInstanceOf(PostWriteEvent::class, $events[1] ?? null);
        self::assertStringContainsString(
            'ERROR: An error occurred while dispatching event. {"event":"Yokai\\\\Batch\\\\Event\\\\PostWriteEvent","error":"RuntimeException: Test exception',
            (string)$execution->getLogs(),
        );
    }
}
