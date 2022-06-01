# Job Launcher

## What is a job launcher ?

The job launcher is responsible for executing/scheduling every jobs.

Yeah, executing OR scheduling. There is multiple implementation of a job launcher across bridges.
Job's execution might be asynchronous, and thus, when you ask the job launcher to "launch" a job,
you have to check the `JobExecution` status that it had returned to know if the job is already executed.

## What is the simplest way to launch a job ?

```php
<?php

declare(strict_types=1);

use Yokai\Batch\Factory\JobExecutionFactory;
use Yokai\Batch\Factory\UniqidJobExecutionIdGenerator;
use Yokai\Batch\Job\JobExecutionAccessor;
use Yokai\Batch\Job\JobExecutor;
use Yokai\Batch\Job\JobInterface;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Launcher\SimpleJobLauncher;
use Yokai\Batch\Registry\JobContainer;
use Yokai\Batch\Registry\JobRegistry;
use Yokai\Batch\Storage\NullJobExecutionStorage;

// you can instead use any psr/container implementation
// @see https://packagist.org/providers/psr/container-implementation
$jobs = new JobContainer([
    'your.job.name' => new class implements JobInterface {
        public function execute(JobExecution $jobExecution): void
        {
            // your business logic
        }
    },
]);
$jobExecutionStorage = new NullJobExecutionStorage();

$launcher = new SimpleJobLauncher(
    new JobExecutionAccessor(new JobExecutionFactory(new UniqidJobExecutionIdGenerator()), $jobExecutionStorage),
    new JobExecutor(new JobRegistry($jobs), $jobExecutionStorage, null),
);

$execution = $launcher->launch('your.job.name', ['job' => ['configuration']]);
```

## What types of launcher exists ?

**Built-in launchers:**
- [SimpleJobLauncher](../../src/Launcher/SimpleJobLauncher.php):
  execute the job directly in the same PHP process.

**Launchers from bridges:**
- [RunCommandJobLauncher (`symfony/console`)](https://github.com/yokai-php/batch-symfony-console/blob/0.x/src/RunCommandJobLauncher.php):
  execute the job via an asynchronous symfony command.
- [DispatchMessageJobLauncher (`symfony/messenger`)](https://github.com/yokai-php/batch-symfony-messenger/blob/0.x/src/DispatchMessageJobLauncher.php):
  execute the job via a symfony message dispatch.

**Launchers for testing purpose:**
- [BufferingJobLauncher](../../src/Test/Launcher/BufferingJobLauncher.php):
  do not execute job, but store execution in a private var that can be accessed afterwards in your tests.

## On the same subject

- [What is a job ?](job.md)
- [What is a job execution ?](job-execution.md)
