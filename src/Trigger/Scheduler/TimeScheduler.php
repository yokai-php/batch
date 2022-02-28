<?php

declare(strict_types=1);

namespace Yokai\Batch\Trigger\Scheduler;

use DateTimeImmutable;
use DateTimeInterface;
use Yokai\Batch\JobExecution;

/**
 * This scheduler implementation uses constructor settings to compute schedules.
 * The main setting is a @see DateTimeInterface that will be converted to a closure,
 * if that date is before job execution start time, the associated job schedule will be triggered.
 *
 * Example :
 *
 *     new CallbackScheduler([
 *         [new DateTimeImmutable('yesterday'), 'job.name'],
 *         [new DateTimeImmutable('yesterday'), 'job.name', ['with' => 'parameters']],
 *         [new DateTimeImmutable('yesterday'), 'job.name', [], 'job_id'],
 *     ]);
 */
final class TimeScheduler extends CallbackScheduler
{
    /**
     * @phpstan-param list<array{0: DateTimeInterface, 1: string, 2: array<string, mixed>|null, 3: string|null}> $config
     */
    public function __construct(array $config)
    {
        $parentConfig = [];
        foreach ($config as $entry) {
            $parentConfig[] = [
                fn(JobExecution $execution) => $entry[0] <= ($execution->getStartTime() ?? new DateTimeImmutable()),
                $entry[1],
                $entry[2] ?? [],
                $entry[3] ?? null,
            ];
        }
        parent::__construct($parentConfig);
    }
}
