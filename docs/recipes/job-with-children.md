# Job with children

todo

```php
<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Yokai\Batch\Job\AbstractJob;
use Yokai\Batch\Job\JobWithChildJobs;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Registry\JobRegistry;
use Yokai\Batch\Serializer\JsonJobExecutionSerializer;
use Yokai\Batch\Storage\FilesystemJobExecutionStorage;

// you should instead use any psr/container implementation
// @see https://packagist.org/providers/psr/container-implementation
$container = new class implements ContainerInterface {
    private array $jobs;

    public function __construct()
    {
        $this->jobs = [
            // here are the jobs you will want to write
            // each job has an identifier so it can be launched later
            'export' => new class extends AbstractJob {
                protected function doExecute(JobExecution $jobExecution): void
                {
                    $exportSince = new DateTimeImmutable($jobExecution->getParameter('since'));
                    // your export logic here
                }
            },
            'upload' => new class extends AbstractJob {
                protected function doExecute(JobExecution $jobExecution): void
                {
                    $pathToUploadExportedFile = $jobExecution->getParameter('path');
                    // your upload logic here
                }
            },
        ];
    }

    public function get(string $id)
    {
        $job = $this->jobs[$id] ?? null;
        if ($job === null) {
            throw new class extends \Exception implements NotFoundExceptionInterface {
            };
        }

        return $job;
    }

    public function has(string $id)
    {
        return isset($this->jobs[$id]);
    }
};

$job = new JobWithChildJobs(
    new FilesystemJobExecutionStorage(new JsonJobExecutionSerializer(), '/dir/where/jobs/are/stored'),
    new JobRegistry($container),
    ['export', 'upload']
);
```
