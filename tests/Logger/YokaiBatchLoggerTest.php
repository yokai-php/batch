<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Logger;

use PHPUnit\Framework\TestCase;
use Yokai\Batch\Event\PostExecuteEvent;
use Yokai\Batch\Event\PreExecuteEvent;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Logger\YokaiBatchLogger;
use Yokai\Batch\Tests\Dummy\DebugEventDispatcher;

final class YokaiBatchLoggerTest extends TestCase
{
    public function testLaunch(): void
    {
        $dispatcher = new DebugEventDispatcher();
        $logger = new YokaiBatchLogger();

        $dispatcher->addListener(PreExecuteEvent::class, $logger->onPreExecute(...));
        $dispatcher->addListener(PostExecuteEvent::class, $logger->onPostExecute(...));

        $execution = JobExecution::createRoot('123', 'test.job_executor');

        $logger->log('info', 'before');
        $preExecuteEvent = new PreExecuteEvent($execution);
        $dispatcher->dispatch($preExecuteEvent);

        $logger->log('info', 'between');

        $postExecuteEvent = new PostExecuteEvent($execution);
        $dispatcher->dispatch($postExecuteEvent);
        $logger->log('info', 'after');

        $logs = $execution->getLogger()->getLogsContent();
        self::assertStringNotContainsString('before', $logs);
        self::assertStringContainsString('between', $logs);
        self::assertStringNotContainsString('after', $logs);
    }
}
