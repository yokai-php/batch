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
         * @phpstan-var array<string, string>
         */
        private array $parameters = [],
        private ?string $id = null,
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
     * @phpstan-return array<string, string>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * An optional job execution id for the job to trigger.
     */
    public function getId(): ?string
    {
        return $this->id;
    }
}
