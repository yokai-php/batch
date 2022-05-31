<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Item\Writer;

use Closure;
use Yokai\Batch\Job\Item\ItemWriterInterface;
use Yokai\Batch\Job\JobExecutionAwareInterface;
use Yokai\Batch\Job\JobExecutionAwareTrait;
use Yokai\Batch\Launcher\JobLauncherInterface;

/**
 * This {@see ItemWriterInterface} will trigger a job for each written item batches.
 */
final class LaunchJobForItemsBatchWriter implements ItemWriterInterface, JobExecutionAwareInterface
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
        if (\is_string($this->parameter)) {
            $parameters = [$this->parameter => $items];
        } else {
            $parameters = ($this->parameter)($items, $this->jobExecution);
        }

        $execution = $this->launcher->launch($this->jobName, $parameters);
        $this->jobExecution->getLogger()->notice(
            'Triggered job for items batch.',
            ['jobName' => $execution->getJobName(), 'executionId' => $execution->getId()]
        );
    }
}
