<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Serializer;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Yokai\Batch\Exception\RuntimeException;
use Yokai\Batch\Factory\JobExecutionLoggerFactory\InMemoryJobExecutionLoggerFactory;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Serializer\JsonJobExecutionSerializer;

final class JsonJobExecutionSerializerTest extends TestCase
{
    #[DataProvider('sets')]
    public function testSerialize(JobExecution $jobExecutionToSerialize, string $expectedSerializedJobExecution): void
    {
        $serializer = new JsonJobExecutionSerializer(new InMemoryJobExecutionLoggerFactory());
        self::assertSame($expectedSerializedJobExecution, $serializer->serialize($jobExecutionToSerialize));
    }

    #[DataProvider('sets')]
    public function testDenormalize(JobExecution $expectedjobExecution, string $serializedJobExecution): void
    {
        $serializer = new JsonJobExecutionSerializer(new InMemoryJobExecutionLoggerFactory());
        self::assertEquals(
            $expectedjobExecution,
            $serializer->unserialize($serializedJobExecution),
        );
    }

    public static function sets(): \Generator
    {
        yield [
            require __DIR__ . '/fixtures/minimal.object.php',
            \json_encode(require __DIR__ . '/fixtures/minimal.array.php'),
        ];
        yield [
            require __DIR__ . '/fixtures/fulfilled.object.php',
            \json_encode(require __DIR__ . '/fixtures/fulfilled.array.php'),
        ];
    }

    #[DataProvider('invalidJobExecutions')]
    public function testSerializeThrowExceptionOnFailure(JobExecution $jobExecutionToSerialize): void
    {
        $this->expectException(RuntimeException::class);

        $serializer = new JsonJobExecutionSerializer(new InMemoryJobExecutionLoggerFactory());
        $serializer->serialize($jobExecutionToSerialize);
    }

    public static function invalidJobExecutions(): \Generator
    {
        $jobExecutionWithResource = JobExecution::createRoot('123', 'test');
        $jobExecutionWithResource->getSummary()->set('fail', \fopen(__FILE__, 'r'));
        yield [$jobExecutionWithResource];
    }

    #[DataProvider('invalidJSON')]
    public function testUnSerializeThrowExceptionOnFailure(string $json): void
    {
        $this->expectException(RuntimeException::class);

        $serializer = new JsonJobExecutionSerializer(new InMemoryJobExecutionLoggerFactory());
        $serializer->unserialize($json);
    }

    public static function invalidJSON(): \Generator
    {
        yield ['malformed JSON'];
        yield ['"json string"'];

        $minimal = require __DIR__ . '/fixtures/minimal.array.php';
        yield [\json_encode(\array_merge($minimal, ['startTime' => 'not a date']))];
        yield [\json_encode(\array_merge($minimal, ['endTime' => 'not a date']))];
        yield [\json_encode(\array_merge($minimal, ['launchedAt' => 'not a date']))];
    }
}
