# Getting started

## Vocabulary

Because when you start with any library
it is important to understand what are the concepts introduced in it.

This is highly recommended that you read this entire page
before starting to work with this library.

- [Job](domain/job.md): where you are going to work as a developer.
- [Item job](domain/item-job.md): A special job which is responsible for batch processing logic.
- [Job Launcher](domain/job-launcher.md): The entry point when you need to execute any job.
- [Job Execution](domain/job-execution.md): The representation of a certain execution of certain job.
- [Job Execution Storage](domain/job-execution-storage.md): The persistence layer of jobs executions.

## Quickstart example

```php
<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Yokai\Batch\Factory\JobExecutionFactory;
use Yokai\Batch\Factory\UniqidJobExecutionIdGenerator;
use Yokai\Batch\Job\AbstractJob;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Launcher\SimpleJobLauncher;
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
            'import' => new class extends AbstractJob {
                protected function doExecute(JobExecution $jobExecution): void
                {
                    $fileToImport = $jobExecution->getParameter('path');
                    // your import logic here
                }
            },
            'export' => new class extends AbstractJob {
                protected function doExecute(JobExecution $jobExecution): void
                {
                    $exportSince = new DateTimeImmutable($jobExecution->getParameter('since'));
                    // your export logic here
                }
            },
        ];
    }

    public function get(string $id)
    {
        $job = $this->jobs[$id] ?? null;
        if ($job === null) {
            throw new class extends \Exception implements NotFoundExceptionInterface {};
        }

        return $job;
    }

    public function has(string $id)
    {
        return isset($this->jobs[$id]);
    }
};

$launcher = new SimpleJobLauncher(
    new JobRegistry($container),
    new JobExecutionFactory(new UniqidJobExecutionIdGenerator()),
    new FilesystemJobExecutionStorage(new JsonJobExecutionSerializer(), '/dir/where/jobs/are/stored'),
    null // or an instance of \Psr\EventDispatcher\EventDispatcherInterface
);

// now you can use $launcher to start any job you registered in $container

$importExecution = $launcher->launch('import', ['path' => '/path/to/file/to/import']);
$exportExecution = $launcher->launch('export', ['since' => '2020-07-03']);
```
