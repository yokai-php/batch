<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Job\Parameters;

use PHPUnit\Framework\TestCase;
use Yokai\Batch\Exception\CannotAccessParameterException;
use Yokai\Batch\Job\Parameters\ClosestJobExecutionAccessor;
use Yokai\Batch\Job\Parameters\JobExecutionParameterAccessor;
use Yokai\Batch\JobExecution;
use Yokai\Batch\JobParameters;

class ClosestJobExecutionAccessorTest extends TestCase
{
    public function test(): void
    {
        $root = JobExecution::createRoot('123', 'root', null, new JobParameters(['level' => 'root']));
        $oneOne = JobExecution::createChild($root, '1.1', null, new JobParameters(['level' => '1.1']));
        $root->addChildExecution($oneOne);
        $oneTwo = JobExecution::createChild($root, '1.2');
        $root->addChildExecution($oneTwo);
        $oneTwoOne = JobExecution::createChild($oneTwo, '1.2.1');
        $oneTwo->addChildExecution($oneTwoOne);
        $oneTwoTwo = JobExecution::createChild($oneTwo, '1.2.2', null, new JobParameters(['level' => '1.2.2']));
        $oneTwo->addChildExecution($oneTwoTwo);

        $accessor = new ClosestJobExecutionAccessor(new JobExecutionParameterAccessor('level'));

        self::assertSame('root', $accessor->get($root));
        self::assertSame('1.1', $accessor->get($oneOne));
        self::assertSame('root', $accessor->get($oneTwo));
        self::assertSame('root', $accessor->get($oneTwoOne));
        self::assertSame('1.2.2', $accessor->get($oneTwoTwo));
    }

    public function testNotFound(): void
    {
        $this->expectException(CannotAccessParameterException::class);

        $root = JobExecution::createRoot('123', 'root');
        $one = JobExecution::createChild($root, '1');
        $root->addChildExecution($one);
        $two = JobExecution::createChild($root, '2');
        $one->addChildExecution($two);

        $accessor = new ClosestJobExecutionAccessor(new JobExecutionParameterAccessor('level'));

        $accessor->get($two);
    }
}
