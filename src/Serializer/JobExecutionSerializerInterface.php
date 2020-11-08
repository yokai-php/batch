<?php

namespace Yokai\Batch\Serializer;

use Yokai\Batch\JobExecution;

interface JobExecutionSerializerInterface
{
    /**
     * @param JobExecution $jobExecution
     *
     * @return string
     */
    public function serialize(JobExecution $jobExecution): string;

    /**
     * @param string $serializedJobExecution
     *
     * @return JobExecution
     */
    public function unserialize(string $serializedJobExecution): JobExecution;

    /**
     * @return string
     */
    public function extension(): string;
}
