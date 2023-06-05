<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Job\Item\Writer;

use PHPUnit\Framework\TestCase;
use Yokai\Batch\Job\Item\Writer\SummaryWriter;
use Yokai\Batch\Job\Parameters\StaticValueParameterAccessor;
use Yokai\Batch\JobExecution;

class SummaryWriterTest extends TestCase
{
    public function test(): void
    {
        $writer = new SummaryWriter(new StaticValueParameterAccessor('target'));
        $writer->setJobExecution($jobExecution = JobExecution::createRoot('123456', 'testing'));

        self::assertSame(null, $jobExecution->getSummary()->get('target'));
        $writer->write(['One']);
        self::assertSame(['One'], $jobExecution->getSummary()->get('target'));
        $writer->write(['Two', 'Three']);
        self::assertSame(['One', 'Two', 'Three'], $jobExecution->getSummary()->get('target'));
    }
}
