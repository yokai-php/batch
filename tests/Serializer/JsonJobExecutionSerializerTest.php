<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Unit\Serializer;

use PHPUnit\Framework\TestCase;
use Yokai\Batch\Exception\RuntimeException;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Serializer\JsonJobExecutionSerializer;

class JsonJobExecutionSerializerTest extends TestCase
{
    /**
     * @dataProvider sets
     */
    public function testSerialize(JobExecution $jobExecutionToSerialize, string $expectedSerializedJobExecution): void
    {
        $serializer = new JsonJobExecutionSerializer();
        self::assertSame($expectedSerializedJobExecution, $serializer->serialize($jobExecutionToSerialize));
    }

    /**
     * @dataProvider sets
     */
    public function testDenormalize(JobExecution $expectedjobExecution, string $serializedJobExecution): void
    {
        $serializer = new JsonJobExecutionSerializer();
        self::assertEquals(
            $expectedjobExecution,
            $serializer->unserialize($serializedJobExecution)
        );
    }

    public function sets(): \Generator
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

    /**
     * @dataProvider invalidJobExecutions
     */
    public function testSerializeThrowExceptionOnFailure(JobExecution $jobExecutionToSerialize): void
    {
        $this->expectException(RuntimeException::class);

        $serializer = new JsonJobExecutionSerializer();
        $serializer->serialize($jobExecutionToSerialize);
    }

    public function invalidJobExecutions(): \Generator
    {
        $jobExecutionWithResource = JobExecution::createRoot('123', 'test');
        $jobExecutionWithResource->getSummary()->set('fail', \fopen(__FILE__, 'r'));
        yield [$jobExecutionWithResource];
    }

    /**
     * @dataProvider invalidJSON
     */
    public function testUnSerializeThrowExceptionOnFailure(string $json): void
    {
        $this->expectException(RuntimeException::class);

        $serializer = new JsonJobExecutionSerializer();
        $serializer->unserialize($json);
    }

    public function invalidJSON(): \Generator
    {
        yield ['malformed JSON'];
        yield ['"json string"'];
    }
}
