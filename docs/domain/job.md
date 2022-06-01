# Job

## What is a job ?

A job is the class that is responsible for **what** your code is doing.

This is the class you will have to create (or reuse),
as it contains the business logic required for what you wish to achieve.

## How to create a job ?

```php
<?php

declare(strict_types=1);

use Yokai\Batch\JobExecution;
use Yokai\Batch\Job\JobInterface;

class DoStuffJob implements JobInterface
{
    public function execute(JobExecution $jobExecution) : void
    {
        // you stuff here
    }
}
```

The only requirement is implementing [`JobInterface`](../../src/Job/JobInterface.php),

## What types of job exists ?

**Built-in jobs:**
- [ItemJob](../../src/Job/Item/ItemJob.php):
  ETL like, batch processing job ([documentation](item-job.md)).
- [JobWithChildJobs](../../src/Job/JobWithChildJobs.php):
  a job that trigger other jobs ([documentation](job-with-children.md)).
- [TriggerScheduledJobsJob](../../src/Trigger/TriggerScheduledJobsJob.php):

**Jobs from bridges:**
- [CopyFilesJob (`league/flysystem`)](https://github.com/yokai-php/batch-league-flysystem/blob/0.x/src/Job/CopyFilesJob.php):
  copy files from one filesystem to another.
- [MoveFilesJob (`league/flysystem`)](https://github.com/yokai-php/batch-league-flysystem/blob/0.x/src/Job/MoveFilesJob.php):
  move files from one filesystem to another.

## On the same subject

- [How do I start a job ?](job-launcher.md)
- [How do I build a batch processing job ?](item-job.md)
- [How do I access parameters of a job ?](job-parameter-accessor.md)
