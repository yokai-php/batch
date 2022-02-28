<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests;

use PHPUnit\Framework\TestCase;
use Yokai\Batch\Exception\UnexpectedValueException;
use Yokai\Batch\Summary;

class SummaryTest extends TestCase
{
    public function testConstruct(): void
    {
        $emptySummary = new Summary();
        $filledSummary = new Summary(['foo' => 'FOO', 'bar' => 'BAR']);

        self::assertSame([], $emptySummary->all());
        self::assertSame(['foo' => 'FOO', 'bar' => 'BAR'], $filledSummary->all());
    }

    public function testSet(): void
    {
        $summary = new Summary(['null' => null]);

        $summary->set('string', 'foo');
        $summary->set('array', []);
        $summary->set('bool', true);
        $summary->set('int', 1);
        $summary->set('float', 0.999);
        self::assertSame(
            ['null' => null, 'string' => 'foo', 'array' => [], 'bool' => true, 'int' => 1, 'float' => 0.999],
            $summary->all()
        );

        $summary->set('string', 'bar');
        self::assertSame(
            ['null' => null, 'string' => 'bar', 'array' => [], 'bool' => true, 'int' => 1, 'float' => 0.999],
            $summary->all()
        );
    }

    public function testIncrement(): void
    {
        $summary = new Summary(['int' => 1, 'float' => 0.999]);
        $summary->increment('int');
        $summary->increment('int', 2);
        $summary->increment('float');
        $summary->increment('float', 2);
        $summary->increment('notset');

        self::assertSame(
            ['int' => 4, 'float' => 3.999, 'notset' => 1],
            $summary->all()
        );
    }

    public function testGet(): void
    {
        $summary = new Summary(
            ['null' => null, 'string' => 'foo', 'array' => [], 'bool' => true, 'int' => 1, 'float' => 0.999]
        );
        $summary->set('string', 'foo');
        $summary->set('array', []);
        $summary->set('bool', true);
        $summary->set('int', 1);
        $summary->set('float', 0.999);

        self::assertSame('foo', $summary->get('string'));
        self::assertSame([], $summary->get('array'));
        self::assertSame(true, $summary->get('bool'));
        self::assertSame(1, $summary->get('int'));
        self::assertSame(0.999, $summary->get('float'));
    }

    public function testClear(): void
    {
        $summary = new Summary(['null' => null, 'string' => 'foo']);
        $summary->clear();

        self::assertSame([], $summary->all());
    }

    public function testGetIterator(): void
    {
        $summary = new Summary(['null' => null, 'string' => 'foo']);

        self::assertSame(['null' => null, 'string' => 'foo'], iterator_to_array($summary));
    }

    public function testCount(): void
    {
        $summary = new Summary(['null' => null, 'string' => 'foo']);

        self::assertCount(2, $summary);

        $summary->clear();

        self::assertCount(0, $summary);
    }

    public function testAppend(): void
    {
        $summary = new Summary(['empty' => [], 'init' => [1, 2]]);
        $summary->append('empty', 1);
        $summary->append('empty', 2);
        $summary->append('empty', 3);
        $summary->append('init', 3);
        $summary->append('undefined', 1);
        $summary->append('undefined', 2);
        $summary->append('undefined', 3);
        self::assertSame([1, 2, 3], $summary->get('empty'));
        self::assertSame([1, 2, 3], $summary->get('init'));
        self::assertSame([1, 2, 3], $summary->get('undefined'));
    }

    public function testAppendNotAnArray(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $summary = new Summary(['init' => 'string']);
        $summary->append('init', 3);
    }
}
