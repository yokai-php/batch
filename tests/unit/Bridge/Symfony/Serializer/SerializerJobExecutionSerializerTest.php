<?php

namespace Yokai\Batch\Tests\Unit\Bridge\Symfony\Serializer;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\SerializerInterface;
use Yokai\Batch\BatchStatus;
use Yokai\Batch\Bridge\Symfony\Serializer\SerializerJobExecutionSerializer;
use Yokai\Batch\JobExecution;

class SerializerJobExecutionSerializerTest extends TestCase
{
    /**
     * @dataProvider variables
     */
    public function testSerialize(
        string $format,
        array $commonContext,
        array $serializeContext,
        array $expectedContext
    ): void {
        $execution = JobExecution::createRoot('123456789', new BatchStatus(BatchStatus::PENDING));

        $symfonySerializer = $this->prophesize(SerializerInterface::class);
        $symfonySerializer->serialize($execution, $format, $expectedContext)
            ->shouldBeCalledTimes(1)
            ->willReturn('serialized job execution');

        $serializer = new SerializerJobExecutionSerializer(
            $symfonySerializer->reveal(),
            $format,
            $commonContext,
            $serializeContext
        );

        self::assertSame('serialized job execution', $serializer->serialize($execution));
    }

    /**
     * @dataProvider variables
     */
    public function testUnserialize(
        string $format,
        array $commonContext,
        array $deserializeContext,
        array $expectedContext
    ): void {
        $execution = JobExecution::createRoot('123456789', new BatchStatus(BatchStatus::PENDING));

        $symfonySerializer = $this->prophesize(SerializerInterface::class);
        $symfonySerializer->deserialize('serialized job execution', JobExecution::class, $format, $expectedContext)
            ->shouldBeCalledTimes(1)
            ->willReturn($execution);

        $serializer = new SerializerJobExecutionSerializer(
            $symfonySerializer->reveal(),
            $format,
            $commonContext,
            [],
            $deserializeContext
        );

        self::assertSame($execution, $serializer->unserialize('serialized job execution'));
    }

    public function variables(): \Generator
    {
        $formats = ['json', 'xml'];
        foreach ($formats as $format) {
            yield [
                $format,
                [], // $commonContext
                [], // $serializeContext or $deserializeContext
                [] // $expectedContext
            ];
            yield [
                $format,
                ['foo' => 'bar'], // $commonContext
                [], // $serializeContext or $deserializeContext
                ['foo' => 'bar'] // $expectedContext
            ];
            yield [
                $format,
                [], // $commonContext
                ['foo' => 'bar'], // $serializeContext or $deserializeContext
                ['foo' => 'bar'] // $expectedContext
            ];
            yield [
                $format,
                ['foo' => 'bar', 'baz' => 'baz'], // $commonContext
                ['foo' => 'foo', 'bar' => 'bar'], // $serializeContext or $deserializeContext
                ['foo' => 'foo', 'bar' => 'bar', 'baz' => 'baz'] // $expectedContext
            ];
        }
    }
}
