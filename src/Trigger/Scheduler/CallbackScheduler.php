<?php

declare(strict_types=1);

namespace Yokai\Batch\Trigger\Scheduler;

use Generator;
use Yokai\Batch\JobExecution;

/**
 * This scheduler implementation uses constructor settings to compute schedules.
 * The main setting is a callback that must return a boolean,
 * if the callback return true, the associated job schedule will be triggered.
 *
 * Example :
 *
 *     new CallbackScheduler([
 *         [fn() => true, 'job.name'],
 *         [fn() => true, 'job.name', ['with' => 'parameters']],
 *         [fn() => true, 'job.name', [], 'job_id'],
 *     ]);
 */
class CallbackScheduler implements SchedulerInterface
{
    /**
     * @phpstan-var list<array{0: callable, 1: string, 2: array<string, mixed>, 3: string|null}>
     */
    private array $config;

    /**
     * @phpstan-param list<array{0: callable, 1: string, 2: array<string, mixed>|null, 3: string|null}> $config
     */
    public function __construct(array $config)
    {
        $this->config = [];
        foreach ($config as $entry) {
            $this->config[] = [
                $entry[0],
                $entry[1],
                $entry[2] ?? [],
                $entry[3] ?? null,
            ];
        }
    }

    /**
     * @inheritdoc
     * @phpstan-return Generator<ScheduledJob>
     */
    public function get(JobExecution $execution): Generator
    {
        /** @var callable $callback */
        /** @var string $job */
        /** @var array $parameters */
        /** @var string|null $id */
        foreach ($this->config as [$callback, $job, $parameters, $id]) {
            if ($callback($execution)) {
                yield new ScheduledJob($job, $parameters, $id);
            }
        }
    }
}
