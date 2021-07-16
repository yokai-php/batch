<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Job\Parameters;

use PHPUnit\Framework\TestCase;
use Yokai\Batch\Job\Parameters\JobExecutionParameterAccessor;
use Yokai\Batch\Job\Parameters\RootJobExecutionAccessor;
use Yokai\Batch\JobExecution;
use Yokai\Batch\JobParameters;

class RootJobExecutionAccessorTest extends TestCase
{
    public function test(): void
    {
        $accessor = new RootJobExecutionAccessor(new JobExecutionParameterAccessor('since'));

        $root = JobExecution::createRoot('123', 'testing', null, new JobParameters(['since' => '2021-07-15']));
        $prepare = JobExecution::createChild($root, 'prepare', null, new JobParameters(['since' => 'unused']));
        $clean = JobExecution::createChild($root, 'clean');
        $root->addChildExecution($prepare);
        $root->addChildExecution($clean);

        self::assertSame('2021-07-15', $accessor->get($root));
        self::assertSame('2021-07-15', $accessor->get($prepare));
        self::assertSame('2021-07-15', $accessor->get($clean));
    }
}
