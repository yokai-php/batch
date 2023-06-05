<?php

declare(strict_types=1);

namespace Yokai\Batch\Storage;

use Throwable;
use Yokai\Batch\Exception\CannotRemoveJobExecutionException;
use Yokai\Batch\Exception\CannotStoreJobExecutionException;
use Yokai\Batch\Exception\FilesystemException;
use Yokai\Batch\Exception\JobExecutionNotFoundException;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Serializer\JobExecutionSerializerInterface;

/**
 * This {@see JobExecutionStorageInterface} do persist {@see JobExecution} on a filesystem.
 * Every {@see JobExecution} will be stored on an individual file,
 * in a dir named with the job name : /path/to/dir/{job name}/{execution id}.{extension}.
 *
 * Example:
 *
 *     /path/to/dir/
 *     ├── import/
 *     │   └── 61519f8e0e868.json
 *     │   └── 61519f8e465a6.json
 *     ├── export/
 *     │   └── 61519f8e0f4a7.json
 *     │   └── 61519f8e46fb3.json
 */
final class FilesystemJobExecutionStorage implements QueryableJobExecutionStorageInterface
{
    public function __construct(
        private JobExecutionSerializerInterface $serializer,
        private string $directory,
    ) {
    }

    public function store(JobExecution $execution): void
    {
        try {
            $this->executionToFile($execution);
        } catch (Throwable $exception) {
            throw new CannotStoreJobExecutionException($execution->getJobName(), $execution->getId(), $exception);
        }
    }

    public function remove(JobExecution $execution): void
    {
        try {
            $path = $this->buildFilePath($execution->getJobName(), $execution->getId());
            if (!file_exists($path)) {
                throw FilesystemException::fileNotFound($path);
            }
            if (!@unlink($path)) {
                throw FilesystemException::cannotRemoveFile($path);
            }
        } catch (Throwable $exception) {
            throw new CannotRemoveJobExecutionException($execution->getJobName(), $execution->getId(), $exception);
        }
    }

    public function retrieve(string $jobName, string $executionId): JobExecution
    {
        try {
            $path = $this->buildFilePath($jobName, $executionId);

            return $this->fileToExecution($path);
        } catch (Throwable $exception) {
            throw new JobExecutionNotFoundException($jobName, $executionId, $exception);
        }
    }

    public function list(string $jobName): iterable
    {
        $glob = new \GlobIterator($this->buildFilePath($jobName, '*'));
        /** @var \SplFileInfo $file */
        foreach ($glob as $file) {
            try {
                yield $this->fileToExecution($file->getPathname());
            } catch (Throwable) {
                // todo should we do something
            }
        }
    }

    public function query(Query $query): iterable
    {
        $candidates = [];
        $glob = new \GlobIterator(
            implode(DIRECTORY_SEPARATOR, [$this->directory, '**', '*']) . '.' . $this->serializer->extension()
        );
        /** @var \SplFileInfo $file */
        foreach ($glob as $file) {
            try {
                $execution = $this->fileToExecution($file->getPathname());
            } catch (Throwable) {
                // todo should we do something
                continue;
            }

            $names = $query->jobs();
            if (count($names) > 0 && !in_array($execution->getJobName(), $names, true)) {
                continue;
            }

            $ids = $query->ids();
            if (count($ids) > 0 && !in_array($execution->getId(), $ids, true)) {
                continue;
            }

            $statuses = $query->statuses();
            if (count($statuses) > 0 && !$execution->getStatus()->isOneOf($statuses)) {
                continue;
            }

            $candidates[] = $execution;
        }

        $order = match ($query->sort()) {
            Query::SORT_BY_START_ASC => static function (JobExecution $left, JobExecution $right): int {
                return $left->getStartTime() <=> $right->getStartTime();
            },
            Query::SORT_BY_START_DESC => static function (JobExecution $left, JobExecution $right): int {
                return $right->getStartTime() <=> $left->getStartTime();
            },
            Query::SORT_BY_END_ASC => static function (JobExecution $left, JobExecution $right): int {
                return $left->getEndTime() <=> $right->getEndTime();
            },
            Query::SORT_BY_END_DESC => static function (JobExecution $left, JobExecution $right): int {
                return $right->getEndTime() <=> $left->getEndTime();
            },
            default => null,
        };

        if ($order) {
            uasort($candidates, $order);
        }

        return array_slice($candidates, $query->offset(), $query->limit());
    }

    private function buildFilePath(string $jobName, string $executionId): string
    {
        return implode(DIRECTORY_SEPARATOR, [$this->directory, $jobName, $executionId]) .
            '.' . $this->serializer->extension();
    }

    private function executionToFile(JobExecution $execution): void
    {
        $path = $this->buildFilePath($execution->getJobName(), $execution->getId());
        $dir = dirname($path);
        if (!is_dir($dir) && false === @mkdir($dir, 0777, true)) {
            throw FilesystemException::cannotCreateDir($path);
        }

        $content = $this->serializer->serialize($execution);

        if (false === @file_put_contents($path, $content)) {
            throw FilesystemException::cannotWriteFile($path);
        }
    }

    private function fileToExecution(string $file): JobExecution
    {
        $content = @file_get_contents($file);
        if ($content === false) {
            throw FilesystemException::cannotReadFile($file);
        }

        return $this->serializer->unserialize($content);
    }
}
