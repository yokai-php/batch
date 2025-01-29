<?php

declare(strict_types=1);

namespace Yokai\Batch\Trigger\Scheduler;

/**
 * This model class is used by schedulers to hold information about a job that should be triggered.
 */
final class ScheduledJob
{
    public function __construct(
        private string $jobName,
        /**
         * @var array<string, mixed>
         */
        private array $parameters = [],
        private string|null $id = null,
    ) {
    }

    /**
     * The job name to trigger.
     */
    public function getJobName(): string
    {
        return $this->jobName;
    }

    /**
     * The job parameters for the job to trigger.
     *
     * @return array<string, mixed>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * An optional job execution id for the job to trigger.
     */
    public function getId(): string|null
    {
        return $this->id;
    }
}
