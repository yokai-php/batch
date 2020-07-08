<?php

namespace Yokai\Batch\Tests\Unit\Bridge\Symfony\Serializer;

use DateTime;
use DateTimeImmutable;
use Generator;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Yokai\Batch\Bridge\Symfony\Serializer\DenormalizeItemProcessor;
use Yokai\Batch\Job\Item\InvalidItemException;

final class DenormalizeItemProcessorTest extends TestCase
{
    /**
     * @var ObjectProphecy|DenormalizerInterface
     */
    private $denormalizer;

    protected function setUp()
    {
        $this->denormalizer = $this->prophesize(DenormalizerInterface::class);
    }

    /**
     * @dataProvider sets
     */
    public function testProcess(string $type, ?string $format, array $context, $item, $expected): void
    {
        $this->denormalizer->supportsDenormalization($item, $type, $format)
            ->shouldBeCalled()
            ->willReturn(true);
        $this->denormalizer->denormalize($item, $type, $format, $context)
            ->shouldBeCalled()
            ->willReturn($expected);

        $processor = new DenormalizeItemProcessor($this->denormalizer->reveal(), $type, $format, $context);

        self::assertSame($expected, $processor->process($item));
    }

    /**
     * @dataProvider sets
     */
    public function testUnsupported(string $type, ?string $format, array $context, $item): void
    {
        $this->expectException(InvalidItemException::class);

        $this->denormalizer->supportsDenormalization($item, $type, $format)
            ->shouldBeCalled()
            ->willReturn(false);
        $this->denormalizer->denormalize(Argument::cetera())
            ->shouldNotBeCalled();

        $processor = new DenormalizeItemProcessor($this->denormalizer->reveal(), $type, $format, $context);

        $processor->process($item);
    }

    /**
     * @dataProvider sets
     */
    public function testException(string $type, ?string $format, array $context, $item): void
    {
        $this->expectException(InvalidItemException::class);

        $this->denormalizer->supportsDenormalization($item, $type, $format)
            ->shouldBeCalled()
            ->willReturn(true);
        $this->denormalizer->denormalize($item, $type, $format, $context)
            ->shouldBeCalled()
            ->willThrow(
                new class extends \Exception implements ExceptionInterface {
                }
            );

        $processor = new DenormalizeItemProcessor($this->denormalizer->reveal(), $type, $format, $context);

        $processor->process($item);
    }

    public function sets(): Generator
    {
        yield [
            'stdClass',
            null,
            [],
            ['foo' => 'bar'],
            \json_decode('{"foo":"bar"}'),
        ];
        yield [
            'DateTime',
            'json',
            [],
            '2020-01-01T12:00:00+02:00',
            DateTime::createFromFormat(\DATE_RFC3339, '2020-01-01T12:00:00+02:00'),
        ];
        yield [
            'DateTimeImmutable',
            'xml',
            ['datetime_format' => \DATE_RSS],
            'Wed, 01 Jan 2020 12:00:00 +0200',
            DateTimeImmutable::createFromFormat(\DATE_RSS, 'Wed, 01 Jan 2020 12:00:00 +0200'),
        ];
    }
}
