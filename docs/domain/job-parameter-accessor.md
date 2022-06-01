# Job parameter accessor

When a job (or a component within a job) can be working with a parameterized value,
it can rely on a [JobParameterAccessorInterface](../../src/Job/Parameters/JobParameterAccessorInterface.php)
instance to retrieve that value.

```php
<?php

declare(strict_types=1);

use Yokai\Batch\Job\JobInterface;
use Yokai\Batch\Job\Parameters\JobParameterAccessorInterface;
use Yokai\Batch\JobExecution;

class FooJob implements JobInterface
{
    public function __construct(
        private JobParameterAccessorInterface $path,
    ) {
    }

    public function execute(JobExecution $jobExecution): void
    {
        /** @var string $path */
        $path = $this->path->get($jobExecution);
        // do something with $path
    }
}
```
## What types of parameter accessors exists ?

**Built-in parameter accessors:**
- [ChainParameterAccessor.php](../../src/Job/Parameters/ChainParameterAccessor.php):
  try multiple parameter accessors, the first that is not failing is used.
- [ClosestJobExecutionAccessor](../../src/Job/Parameters/ClosestJobExecutionAccessor.php):
  try another parameter accessor on each job execution in hierarchy, until not failed.
- [DefaultParameterAccessor](../../src/Job/Parameters/DefaultParameterAccessor.php):
  try accessing parameter using another parameter accessor, use default value if failed.
- [JobExecutionParameterAccessor](../../src/Job/Parameters/JobExecutionParameterAccessor.php):
  extract value from job execution's [parameters](../../src/JobParameters.php).
- [JobExecutionSummaryAccessor](../../src/Job/Parameters/JobExecutionSummaryAccessor.php):
  extract value from job execution's [summary](../../src/Summary.php).
- [ParentJobExecutionAccessor](../../src/Job/Parameters/ParentJobExecutionAccessor.php):
  use another parameter accessor on job execution's parent execution.
- [ReplaceWithVariablesParameterAccessor](../../src/Job/Parameters/ReplaceWithVariablesParameterAccessor.php):
  use another parameter accessor to get string value, and replace variables before returning.
- [RootJobExecutionAccessor](../../src/Job/Parameters/RootJobExecutionAccessor.php):
  use another parameter accessor on job execution's root execution.
- [SiblingJobExecutionAccessor](../../src/Job/Parameters/SiblingJobExecutionAccessor.php):
  use another parameter accessor on job execution's sibling execution.
- [StaticValueParameterAccessor](../../src/Job/Parameters/StaticValueParameterAccessor.php):
  use static value provided at construction.

**Parameter accessors from bridges:**
- [ContainerParameterAccessor (`symfony/framework-bundle`)](https://github.com/yokai-php/batch-symfony-framework/blob/0.x/src/ContainerParameterAccessor.php):
  use a parameter from Symfony's container.

## On the same subject

- [What is a job ?](job.md)
- [When does a job execution hierarchy is created ?](job-with-children.md)
- [What is a job execution ?](job-execution.md)
