<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Job\Item\Writer;

use ArrayIterator;
use DateTime;
use DateTimeImmutable;
use IteratorIterator;
use PHPUnit\Framework\TestCase;
use Throwable;
use Traversable;
use Yokai\Batch\Exception\UnexpectedValueException;
use Yokai\Batch\Job\Item\Writer\RoutingWriter;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Finder\ClassMapFinder;
use Yokai\Batch\Test\Finder\DummyFinder;
use Yokai\Batch\Test\Job\Item\Writer\InMemoryWriter;
use Yokai\Batch\Test\Job\Item\Writer\TestDebugWriter;

class RoutingWriterTest extends TestCase
{
    public function test(): void
    {
        $datesWriter = new TestDebugWriter($datesWriterInner = new InMemoryWriter());
        $traversableWriter = new TestDebugWriter($traversableWriterInner = new InMemoryWriter());
        $notCalledWriter = new TestDebugWriter($notCalledWriterInner = new InMemoryWriter());
        $defaultWriter = new TestDebugWriter($defaultWriterInner = new InMemoryWriter());
        $writer = new RoutingWriter(new ClassMapFinder([
            DateTime::class => $datesWriter,
            DateTimeImmutable::class => $datesWriter,
            Traversable::class => $traversableWriter,
            Throwable::class => $notCalledWriter,
        ], $defaultWriter));

        $jobExecution = JobExecution::createRoot('123456', 'testing');

        $writer->setJobExecution($jobExecution);
        $writer->initialize();
        $writer->write([
            $january = new DateTime('2021-01-01'),
            $february = new DateTimeImmutable('2021-02-01'),
            $march = '2021-03-01',
            $april = new DateTime('2021-04-01'),
            $may = new DateTimeImmutable('2021-05-01'),
            $june = '2021-06-01',
            $julyToSeptember = new ArrayIterator(['2021-07-01', '2021-08-01', '2021-09-01']),
            $october = new DateTime('2021-10-01'),
            $novemberAndDecember = new IteratorIterator(new ArrayIterator(['2021-11-01', '2021-12-01'])),
        ]);
        $writer->flush();

        self::assertSame([$january, $february, $april, $may, $october], $datesWriterInner->getItems());
        self::assertSame([$julyToSeptember, $novemberAndDecember], $traversableWriterInner->getItems());
        self::assertSame([], $notCalledWriterInner->getItems());
        self::assertSame([$march, $june], $defaultWriterInner->getItems());

        self::assertTrue($datesWriter->wasInitialized());
        self::assertTrue($datesWriter->wasFlushed());
        self::assertTrue($datesWriter->wasWritten());
        self::assertTrue($traversableWriter->wasInitialized());
        self::assertTrue($traversableWriter->wasFlushed());
        self::assertTrue($traversableWriter->wasWritten());
        self::assertFalse($notCalledWriter->wasInitialized());
        self::assertFalse($notCalledWriter->wasFlushed());
        self::assertFalse($notCalledWriter->wasWritten());
        self::assertTrue($defaultWriter->wasInitialized());
        self::assertTrue($defaultWriter->wasFlushed());
        self::assertTrue($defaultWriter->wasWritten());
    }

    /**
     * Finder must return ItemProcessorInterface, otherwise an exception will be thrown.
     */
    public function testMisconfiguredFinder(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $writer = new RoutingWriter(new DummyFinder(new \stdClass()));
        $writer->write(['anything']);
    }
}
