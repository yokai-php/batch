# Job with children

todo

```php
<?php

declare(strict_types=1);

use Yokai\Batch\Job\JobExecutor;
use Yokai\Batch\Job\JobInterface;
use Yokai\Batch\Job\JobWithChildJobs;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Registry\JobContainer;
use Yokai\Batch\Registry\JobRegistry;
use Yokai\Batch\Storage\NullJobExecutionStorage;

// you can instead use any psr/container implementation
// @see https://packagist.org/providers/psr/container-implementation
$container = new JobContainer([
    // here are the jobs you will want to write
    // each job has an identifier so it can be launched later
    'export' => new class implements JobInterface {
        public function execute(JobExecution $jobExecution): void
        {
            $exportSince = new DateTimeImmutable($jobExecution->getParameter('since'));
            // your export logic here
        }
    },
    'upload' => new class implements JobInterface {
        public function execute(JobExecution $jobExecution): void
        {
            $pathToUploadExportedFile = $jobExecution->getParameter('path');
            // your upload logic here
        }
    },
]);

$job = new JobWithChildJobs(
    $jobExecutionStorage = new NullJobExecutionStorage(),
    new JobExecutor(new JobRegistry($container), $jobExecutionStorage, null),
    ['export', 'upload']
);
```
