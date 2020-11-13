<?php

declare(strict_types=1);

namespace Yokai\Batch\Serializer;

use Yokai\Batch\Exception\InvalidArgumentException;
use Yokai\Batch\Exception\RuntimeException;
use Yokai\Batch\JobExecution;

interface JobExecutionSerializerInterface
{
    /**
     * @param JobExecution $jobExecution
     *
     * @return string
     * @throws RuntimeException
     */
    public function serialize(JobExecution $jobExecution): string;

    /**
     * @param string $serializedJobExecution
     *
     * @return JobExecution
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function unserialize(string $serializedJobExecution): JobExecution;

    /**
     * @return string
     */
    public function extension(): string;
}
