<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Item\Writer;

use Closure;
use Yokai\Batch\Job\Item\ItemWriterInterface;
use Yokai\Batch\Job\JobExecutionAwareInterface;
use Yokai\Batch\Job\JobExecutionAwareTrait;
use Yokai\Batch\Launcher\JobLauncherInterface;

/**
 * This {@see ItemWriterInterface} will trigger a job for each written items.
 */
final class LaunchJobForEachItemWriter implements ItemWriterInterface, JobExecutionAwareInterface
{
    use JobExecutionAwareTrait;

    public function __construct(
        private JobLauncherInterface $launcher,
        private string $jobName,
        private string|Closure $parameter,
    ) {
    }

    public function write(iterable $items): void
    {
        foreach ($items as $item) {
            if (\is_string($this->parameter)) {
                $parameters = [$this->parameter => $item];
            } else {
                $parameters = ($this->parameter)($item, $this->jobExecution);
            }

            $execution = $this->launcher->launch($this->jobName, $parameters);
            $this->jobExecution->getLogger()->notice(
                'Triggered job for item.',
                ['jobName' => $execution->getJobName(), 'executionId' => $execution->getId()]
            );
        }
    }
}
