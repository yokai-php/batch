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
use Yokai\Batch\Job\AbstractJob;

class DoStuffJob extends AbstractJob
{
    protected function doExecute(JobExecution $jobExecution) : void
    {
        // you stuff here
    }
}
```

It has to implement [`JobInterface`](../../src/Job/JobInterface.php),
but it is recommended that you extends [`AbstractJob`](../../src/Job/AbstractJob.php), 
that will handle for you :
- catching any exceptions
- ensure status is allows execution
- change status on success/failure
- set execution start time
- set execution end time

## On the same subject

- [How do I start a job ?](job-launcher.md)
- [How do I build a batch processing job ?](item-job.md)
