<?php

declare(strict_types=1);

namespace Yokai\Batch\Serializer;

use Yokai\Batch\Exception\InvalidArgumentException;
use Yokai\Batch\Exception\RuntimeException;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Storage\JobExecutionStorageInterface;

/**
 * The serializer is responsible for transforming {@see JobExecution} to string, and the other way round.
 * It is used by some {@see JobExecutionStorageInterface} during persistence operations.
 */
interface JobExecutionSerializerInterface
{
    /**
     * Transform a {@see JobExecution} to a string.
     *
     * @throws RuntimeException
     */
    public function serialize(JobExecution $jobExecution): string;

    /**
     * Transform a string to a {@see JobExecution}.
     *
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function unserialize(string $serializedJobExecution): JobExecution;

    /**
     * Tells the file extension attached to this serializer.
     */
    public function extension(): string;
}
