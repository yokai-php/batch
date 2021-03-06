# Create a Job Execution Storage

```php
<?php

declare(strict_types=1);

use Yokai\Batch\Exception\JobExecutionNotFoundException;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Storage\JobExecutionStorageInterface;

class InMemoryJobExecutionStorage implements JobExecutionStorageInterface
{
    private array $memory = [];

    public function store(JobExecution $execution) : void
    {
        $this->memory[$execution->getJobName()][$execution->getId()] = $execution;
    }

    public function remove(JobExecution $execution) : void
    {
        unset(
            $this->memory[$execution->getJobName()][$execution->getId()]
        );
    }

    public function retrieve(string $jobName, string $executionId) : JobExecution
    {
        $execution = $this->memory[$jobName][$executionId] ?? null;
        if ($execution === null) {
            throw new JobExecutionNotFoundException($jobName, $executionId);
        }

        return $execution;
    }
}
```

## On the same subject

- [What is a Job Execution Storage ?](../domain/job-execution-storage.md)
