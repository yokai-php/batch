<?php

declare(strict_types=1);

namespace Yokai\Batch\Bridge\Symfony\Serializer;

use Symfony\Component\Serializer\SerializerInterface;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Serializer\JobExecutionSerializerInterface;

final class SerializerJobExecutionSerializer implements JobExecutionSerializerInterface
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var string
     */
    private $format;

    /**
     * @var array
     */
    private $commonContext;

    /**
     * @var array
     */
    private $serializeContext;

    /**
     * @var array
     */
    private $deserializeContext;

    public function __construct(
        SerializerInterface $serializer,
        string $format,
        array $commonContext = [],
        array $serializeContext = [],
        array $deserializeContext = []
    ) {
        $this->serializer = $serializer;
        $this->format = $format;
        $this->commonContext = $commonContext;
        $this->serializeContext = $serializeContext;
        $this->deserializeContext = $deserializeContext;
    }

    /**
     * @inheritdoc
     */
    public function serialize(JobExecution $jobExecution): string
    {
        return $this->serializer->serialize(
            $jobExecution,
            $this->format,
            array_merge($this->commonContext, $this->serializeContext)
        );
    }

    /**
     * @inheritdoc
     */
    public function unserialize(string $serializedJobExecution): JobExecution
    {
        return $this->serializer->deserialize(
            $serializedJobExecution,
            JobExecution::class,
            $this->format,
            array_merge($this->commonContext, $this->deserializeContext)
        );
    }
}
