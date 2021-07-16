<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Job\Parameters;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Yokai\Batch\Job\Parameters\ReplaceWithVariablesParameterAccessor;
use Yokai\Batch\Job\Parameters\StaticValueParameterAccessor;
use Yokai\Batch\JobExecution;

class ReplaceWithVariablesParameterAccessorTest extends TestCase
{
    public function test(): void
    {
        $accessor = new ReplaceWithVariablesParameterAccessor(
            new StaticValueParameterAccessor('{job}/{id}/{date}/{seed}')
        );

        $execution = JobExecution::createRoot('123', 'testing-1');
        $execution->setStartTime(new DateTimeImmutable('2021-07-15 11:07:28'));
        self::assertMatchesRegularExpression(
            '~^testing-1/123/20210715110728/[a-z0-9]{13}$~',
            $accessor->get($execution)
        );

        $execution = JobExecution::createRoot('456', 'testing-2');
        $execution->setStartTime(new DateTimeImmutable('1986-11-17 22:30:58'));
        self::assertMatchesRegularExpression(
            '~^testing-2/456/19861117223058/[a-z0-9]{13}$~',
            $accessor->get($execution)
        );
    }

    public function testConstructorOverride(): void
    {
        $accessor = new ReplaceWithVariablesParameterAccessor(
            new StaticValueParameterAccessor('{job}/{id}/{date}/{seed}/{extra}'),
            [
                '{job}' => 'job overridden',
                '{id}' => 'id overridden',
                '{date}' => 'date overridden',
                '{seed}' => 'seed overridden',
                '{extra}' => 'extra',
            ]
        );

        $execution = JobExecution::createRoot('123', 'testing-1');
        $execution->setStartTime(new DateTimeImmutable('2021-07-15 11:07:28'));
        self::assertSame(
            'job overridden/id overridden/date overridden/seed overridden/extra',
            $accessor->get($execution)
        );

        $execution = JobExecution::createRoot('456', 'testing-2');
        $execution->setStartTime(new DateTimeImmutable('1986-11-17 22:30:58'));
        self::assertSame(
            'job overridden/id overridden/date overridden/seed overridden/extra',
            $accessor->get($execution)
        );
    }

    public function testNoPlaceholders(): void
    {
        $accessor = new ReplaceWithVariablesParameterAccessor(
            new StaticValueParameterAccessor('string without placeholder')
        );

        self::assertSame(
            'string without placeholder',
            $accessor->get(JobExecution::createRoot('123', 'testing'))
        );
    }

    public function testNotString(): void
    {
        $accessor = new ReplaceWithVariablesParameterAccessor(
            new StaticValueParameterAccessor(1042)
        );

        self::assertSame(
            1042,
            $accessor->get(JobExecution::createRoot('123', 'testing'))
        );
    }
}
