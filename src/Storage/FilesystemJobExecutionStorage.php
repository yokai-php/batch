<?php

declare(strict_types=1);

namespace Yokai\Batch\Storage;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
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
final readonly class FilesystemJobExecutionStorage implements QueryableJobExecutionStorageInterface
{
    public function __construct(
        private JobExecutionSerializerInterface $serializer,
        private string $directory,
        private LoggerInterface $logger = new NullLogger(),
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
            if (!\file_exists($path)) {
                throw FilesystemException::fileNotFound($path);
            }
            if (!@\unlink($path)) {
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
            } catch (Throwable $exception) {
                $this->logger->warning(
                    'Failed to read job execution file, skipping.',
                    ['file' => $file->getPathname(), 'exception' => $exception],
                );
            }
        }
    }

    public function query(Query $query): iterable
    {
        $jobExecutions = $this->rawQuery($query);

        $order = match ($query->sort()) {
            Query::SORT_BY_START_ASC => static fn(JobExecution $left, JobExecution $right): int => $left->getStartTime() <=> $right->getStartTime(),
            Query::SORT_BY_START_DESC => static fn(JobExecution $left, JobExecution $right): int => $right->getStartTime() <=> $left->getStartTime(),
            Query::SORT_BY_END_ASC => static fn(JobExecution $left, JobExecution $right): int => $left->getEndTime() <=> $right->getEndTime(),
            Query::SORT_BY_END_DESC => static fn(JobExecution $left, JobExecution $right): int => $right->getEndTime() <=> $left->getEndTime(),
            default => null,
        };

        if ($order) {
            \uasort($jobExecutions, $order);
        }

        return \array_slice($jobExecutions, $query->offset(), $query->limit());
    }

    public function count(Query $query): int
    {
        $jobExecutions = $this->rawQuery($query);

        return \count($jobExecutions);
    }

    public function purge(Query $query): void
    {
        foreach ($this->rawQuery($query) as $execution) {
            $this->remove($execution);
        }
    }

    /**
     * @return list<JobExecution>
     */
    private function rawQuery(Query $query): array
    {
        $jobExecutions = [];
        $glob = new \GlobIterator(
            \implode(DIRECTORY_SEPARATOR, [$this->directory, '**', '*']) . '.' . $this->serializer->extension(),
        );
        /** @var \SplFileInfo $file */
        foreach ($glob as $file) {
            try {
                $execution = $this->fileToExecution($file->getPathname());
            } catch (Throwable $exception) {
                $this->logger->warning(
                    'Failed to read job execution file, skipping.',
                    ['file' => $file->getPathname(), 'exception' => $exception],
                );

                continue;
            }

            $names = $query->jobs();
            if ($names !== [] && !\in_array($execution->getJobName(), $names, true)) {
                continue;
            }

            $ids = $query->ids();
            if ($ids !== [] && !\in_array($execution->getId(), $ids, true)) {
                continue;
            }

            $statuses = $query->statuses();
            if ($statuses !== [] && !$execution->getStatus()->isOneOf($statuses)) {
                continue;
            }

            $startTime = $execution->getStartTime();
            $startDateFrom = $query->startTime()?->getFrom();
            if ($startDateFrom !== null && ($startTime === null || $startTime < $startDateFrom)) {
                continue;
            }
            $startDateTo = $query->startTime()?->getTo();
            if ($startDateTo !== null && ($startTime === null || $startTime > $startDateTo)) {
                continue;
            }

            $endTime = $execution->getEndTime();
            $endDateFrom = $query->endTime()?->getFrom();
            if ($endDateFrom !== null && ($endTime === null || $endTime < $endDateFrom)) {
                continue;
            }
            $endDateTo = $query->endTime()?->getTo();
            if ($endDateTo !== null && ($endTime === null || $endTime > $endDateTo)) {
                continue;
            }

            $jobExecutions[] = $execution;
        }

        return $jobExecutions;
    }

    private function buildFilePath(string $jobName, string $executionId): string
    {
        return \implode(DIRECTORY_SEPARATOR, [$this->directory, $jobName, $executionId]) .
            '.' . $this->serializer->extension();
    }

    private function executionToFile(JobExecution $execution): void
    {
        $path = $this->buildFilePath($execution->getJobName(), $execution->getId());
        $dir = \dirname($path);
        if (!\is_dir($dir) && @\mkdir($dir, 0777, true) === false) {
            throw FilesystemException::cannotCreateDir($path);
        }

        $content = $this->serializer->serialize($execution);

        if (@\file_put_contents($path, $content) === false) {
            throw FilesystemException::cannotWriteFile($path);
        }
    }

    private function fileToExecution(string $file): JobExecution
    {
        $content = @\file_get_contents($file);
        if ($content === false) {
            throw FilesystemException::cannotReadFile($file);
        }

        return $this->serializer->unserialize($content);
    }
}
