<?php

namespace Yokai\Batch\Tests\Unit\Bridge\Symfony\Serializer;

use PHPUnit\Framework\TestCase;
use Yokai\Batch\Bridge\Symfony\Serializer\JobExecutionNormalizer;
use Yokai\Batch\JobExecution;

class JobExecutionNormalizerTest extends TestCase
{
    /**
     * @dataProvider sets
     */
    public function testNormalize(JobExecution $jobExecutionToNormalize, array $expectedNormalizedJobExecution): void
    {
        $normalizer = new JobExecutionNormalizer();
        self::assertTrue($normalizer->supportsNormalization($jobExecutionToNormalize));
        self::assertSame($expectedNormalizedJobExecution, $normalizer->normalize($jobExecutionToNormalize));
    }

    /**
     * @dataProvider sets
     */
    public function testDenormalize(JobExecution $expectedjobExecution, array $normalizedJobExecution): void
    {
        $normalizer = new JobExecutionNormalizer();
        self::assertTrue($normalizer->supportsDenormalization($normalizedJobExecution, JobExecution::class));
        self::assertEquals($expectedjobExecution, $normalizer->denormalize($normalizedJobExecution, JobExecution::class));
    }

    public function sets(): \Generator
    {
        yield [
            require __DIR__.'/fixtures/normalizer/minimal-denormalized.php',
            require __DIR__.'/fixtures/normalizer/minimal-normalized.php',
        ];
        yield [
            require __DIR__.'/fixtures/normalizer/fulfilled-denormalized.php',
            require __DIR__.'/fixtures/normalizer/fulfilled-normalized.php',
        ];
    }
}
