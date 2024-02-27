<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Logger;

use PHPUnit\Framework\TestCase;
use Yokai\Batch\Event\PostExecuteEvent;
use Yokai\Batch\Event\PreExecuteEvent;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Logger\BatchLogger;
use Yokai\Batch\Tests\Dummy\DebugEventDispatcher;

class BatchLoggerTest extends TestCase
{
    public function testLaunch(): void
    {
        $dispatcher = new DebugEventDispatcher();
        $logger = new BatchLogger();

        $dispatcher->addListener(PreExecuteEvent::class, [$logger, 'onPreExecute']);
        $dispatcher->addListener(PostExecuteEvent::class, [$logger, 'onPostExecute']);

        $execution = JobExecution::createRoot('123', 'test.job_executor');

        $logger->log('info', 'before');
        $preExecuteEvent = new PreExecuteEvent($execution);
        $dispatcher->dispatch($preExecuteEvent);

        $logger->log('info', 'between');

        $postExecuteEvent = new PostExecuteEvent($execution);
        $dispatcher->dispatch($postExecuteEvent);
        $logger->log('info', 'after');

        self::assertStringNotContainsString('before', $execution->getLogs()->__toString());
        self::assertStringContainsString('between', $execution->getLogs()->__toString());
        self::assertStringNotContainsString('after', $execution->getLogs()->__toString());
    }
}
