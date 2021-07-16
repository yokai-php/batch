<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Job\Parameters;

use PHPUnit\Framework\TestCase;
use Yokai\Batch\Exception\CannotAccessParameterException;
use Yokai\Batch\Job\Parameters\JobExecutionSummaryAccessor;
use Yokai\Batch\Job\Parameters\SiblingJobExecutionAccessor;
use Yokai\Batch\JobExecution;

class SiblingJobExecutionAccessorTest extends TestCase
{
    public function test(): void
    {
        $root = JobExecution::createRoot('123', 'root');
        $prepare = JobExecution::createChild($root, 'prepare');
        $prepare->getSummary()->set('prepared', 1042);
        $root->addChildExecution($prepare);
        $do = JobExecution::createChild($root, 'do');
        $root->addChildExecution($do);
        $clean = JobExecution::createChild($root, 'clean');
        $root->addChildExecution($clean);

        $accessor = new SiblingJobExecutionAccessor(new JobExecutionSummaryAccessor('prepared'), 'prepare');

        self::assertSame(1042, $accessor->get($prepare));
        self::assertSame(1042, $accessor->get($do));
        self::assertSame(1042, $accessor->get($clean));
    }

    public function testNoParent(): void
    {
        $this->expectException(CannotAccessParameterException::class);

        $root = JobExecution::createRoot('123', 'root');

        $accessor = new SiblingJobExecutionAccessor(new JobExecutionSummaryAccessor('prepared'), 'prepare');

        $accessor->get($root);
    }

    public function testSiblingNotFound(): void
    {
        $this->expectException(CannotAccessParameterException::class);

        $root = JobExecution::createRoot('123', 'root');
        $do = JobExecution::createChild($root, 'do');
        $root->addChildExecution($do);

        $accessor = new SiblingJobExecutionAccessor(new JobExecutionSummaryAccessor('prepared'), 'prepare');

        $accessor->get($do);
    }
}
