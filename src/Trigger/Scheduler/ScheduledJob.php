<?php

declare(strict_types=1);

namespace Yokai\Batch\Trigger\Scheduler;

/**
 * This model class is used by schedulers to hold information about a job that should be triggered.
 */
final class ScheduledJob
{
    private string $jobName;
    /**
     * @phpstan-var array<string, string>
     */
    private array $parameters;
    private ?string $id;

    /**
     * @phpstan-param array<string, string> $parameters
     */
    public function __construct(string $jobName, array $parameters = [], string $id = null)
    {
        $this->jobName = $jobName;
        $this->parameters = $parameters;
        $this->id = $id;
    }

    /**
     * The job name to trigger.
     *
     * @return string
     */
    public function getJobName(): string
    {
        return $this->jobName;
    }

    /**
     * The job parameters for the job to trigger.
     *
     * @return array
     * @phpstan-return array<string, string>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * An optional job execution id for the job to trigger.
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }
}
