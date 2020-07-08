<?php

namespace Yokai\Batch\Tests\Unit\Bridge\Symfony\Serializer;

use DateTime;
use DateTimeImmutable;
use Generator;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Yokai\Batch\Bridge\Symfony\Serializer\NormalizeItemProcessor;
use Yokai\Batch\Job\Item\InvalidItemException;

final class NormalizeItemProcessorTest extends TestCase
{
    /**
     * @var ObjectProphecy|NormalizerInterface
     */
    private $normalizer;

    protected function setUp()
    {
        $this->normalizer = $this->prophesize(NormalizerInterface::class);
    }

    /**
     * @dataProvider sets
     */
    public function testProcess(?string $format, array $context, $item, $expected): void
    {
        $this->normalizer->supportsNormalization($item, $format)
            ->shouldBeCalled()
            ->willReturn(true);
        $this->normalizer->normalize($item, $format, $context)
            ->shouldBeCalled()
            ->willReturn($expected);

        $processor = new NormalizeItemProcessor($this->normalizer->reveal(), $format, $context);

        self::assertSame($expected, $processor->process($item));
    }

    /**
     * @dataProvider sets
     */
    public function testUnsupported(?string $format, array $context, $item): void
    {
        $this->expectException(InvalidItemException::class);

        $this->normalizer->supportsNormalization($item, $format)
            ->shouldBeCalled()
            ->willReturn(false);
        $this->normalizer->normalize(Argument::cetera())
            ->shouldNotBeCalled();

        $processor = new NormalizeItemProcessor($this->normalizer->reveal(), $format, $context);

        $processor->process($item);
    }

    /**
     * @dataProvider sets
     */
    public function testException(?string $format, array $context, $item): void
    {
        $this->expectException(InvalidItemException::class);

        $this->normalizer->supportsNormalization($item, $format)
            ->shouldBeCalled()
            ->willReturn(true);
        $this->normalizer->normalize($item, $format, $context)
            ->shouldBeCalled()
            ->willThrow(
                new class extends \Exception implements ExceptionInterface {
                }
            );

        $processor = new NormalizeItemProcessor($this->normalizer->reveal(), $format, $context);

        $processor->process($item);
    }

    public function sets(): Generator
    {
        yield [
            null,
            [],
            \json_decode('{"foo":"bar"}'),
            ['foo' => 'bar'],
        ];
        yield [
            'json',
            [],
            DateTime::createFromFormat(\DATE_RFC3339, '2020-01-01T12:00:00+02:00'),
            '2020-01-01T12:00:00+02:00',
        ];
        yield [
            'xml',
            ['datetime_format' => \DATE_RSS],
            DateTimeImmutable::createFromFormat(\DATE_RSS, 'Wed, 01 Jan 2020 12:00:00 +0200'),
            'Wed, 01 Jan 2020 12:00:00 +0200',
        ];
    }
}
