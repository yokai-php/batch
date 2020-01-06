<?php declare(strict_types=1);

namespace Yokai\Batch\Storage;

use Throwable;
use Yokai\Batch\Exception\CannotRemoveJobExecutionException;
use Yokai\Batch\Exception\CannotStoreJobExecutionException;
use Yokai\Batch\Exception\JobExecutionNotFoundException;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Serializer\JobExecutionSerializerInterface;

final class FilesystemJobExecutionStorage implements QueryableJobExecutionStorageInterface
{
    /**
     * @var JobExecutionSerializerInterface
     */
    private $serializer;

    /**
     * @var string
     */
    private $directory;

    /**
     * @var string
     */
    private $extension;

    /**
     * @param JobExecutionSerializerInterface $serializer
     * @param string                          $directory
     * @param string                          $extension
     */
    public function __construct(JobExecutionSerializerInterface $serializer, string $directory, string $extension)
    {
        $this->serializer = $serializer;
        $this->directory = $directory;
        $this->extension = $extension;
    }

    /**
     * @inheritDoc
     */
    public function store(JobExecution $execution): void
    {
        try {
            $this->executionToFile($execution);
        } catch (Throwable $exception) {
            throw new CannotStoreJobExecutionException($execution->getJobName(), $execution->getId(), $exception);
        }
    }

    /**
     * @inheritDoc
     */
    public function remove(JobExecution $execution): void
    {
        try {
            $path = $this->buildFilePath($execution->getJobName(), $execution->getId());
            if (!file_exists($path)) {
                throw new \RuntimeException(sprintf('File "%s" does not exists.', $path));
            }
            if (!@unlink($path)) {
                throw new \RuntimeException(sprintf('Unable to remove file "%s".', $path));
            }
        } catch (Throwable $exception) {
            throw new CannotRemoveJobExecutionException($execution->getJobName(), $execution->getId(), $exception);
        }
    }

    /**
     * @inheritDoc
     */
    public function retrieve(string $jobName, string $executionId): JobExecution
    {
        try {
            $path = $this->buildFilePath($jobName, $executionId);

            return $this->fileToExecution($path);
        } catch (Throwable $exception) {
            throw new JobExecutionNotFoundException($jobName, $executionId, $exception);
        }
    }

    /**
     * @inheritDoc
     */
    public function list(string $jobName): iterable
    {
        $glob = new \GlobIterator($this->buildFilePath($jobName, '*'));
        /** @var \SplFileInfo $file */
        foreach ($glob as $file) {
            try {
                yield $this->fileToExecution($file->getRealPath());
            } catch (Throwable $exception) {
                // todo should we do something
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function query(Query $query): iterable
    {
        $candidates = [];
        $glob = new \GlobIterator(implode(DIRECTORY_SEPARATOR, [$this->directory, '**', '*']).'.'.$this->extension);
        foreach ($glob as $file) {
            try {
                $execution = $this->fileToExecution($file->getRealPath());
            } catch (Throwable $exception) {
                // todo should we do something
                continue;
            }

            $names = $query->jobs();
            if (count($names) > 0 && !in_array($execution->getJobName(), $names)) {
                continue;
            }

            $ids = $query->ids();
            if (count($ids) > 0 && !in_array($execution->getId(), $ids)) {
                continue;
            }

            $statuses = $query->statuses();
            if (count($statuses) > 0 && !$execution->getStatus()->isOneOf($statuses)) {
                continue;
            }

            $candidates[] = $execution;
        }

        $order = null;
        switch ($query->sort()) {
            case Query::SORT_BY_START_ASC:
                $order = function (JobExecution $left, JobExecution $right): int {
                    return $left->getStartTime() <=> $right->getStartTime();
                };
                break;
            case Query::SORT_BY_START_DESC:
                $order = function (JobExecution $left, JobExecution $right): int {
                    return $right->getStartTime() <=> $left->getStartTime();
                };
                break;
            case Query::SORT_BY_END_ASC:
                $order = function (JobExecution $left, JobExecution $right): int {
                    return $left->getEndTime() <=> $right->getEndTime();
                };
                break;
            case Query::SORT_BY_END_DESC:
                $order = function (JobExecution $left, JobExecution $right): int {
                    return $right->getEndTime() <=> $left->getEndTime();
                };
                break;
        }

        if ($order) {
            uasort($candidates, $order);
        }

        return array_slice(
            $candidates,
            $query->offset(),
            $query->limit()
        );
    }

    /**
     * @param string $jobName
     * @param string $executionId
     *
     * @return string
     */
    public function buildFilePath(string $jobName, string $executionId): string
    {
        return implode(DIRECTORY_SEPARATOR, [$this->directory, $jobName, $executionId]).'.'.$this->extension;
    }

    private function executionToFile(JobExecution $execution): void
    {
        $path = $this->buildFilePath($execution->getJobName(), $execution->getId());
        $dir = dirname($path);
        if (!is_dir($dir) && false === @mkdir($dir, 0777, true)) {
            throw new \RuntimeException(sprintf('Cannot create dir "%s".', $path));
        }

        $content = $this->serializer->serialize($execution);

        if (false === file_put_contents($path, $content)) {
            throw new \RuntimeException(sprintf('Cannot write content to file "%s".', $path));
        }
    }

    private function fileToExecution(string $file): JobExecution
    {
        if (!file_exists($file)) {
            throw new \RuntimeException(sprintf('File "%s" does not exists.', $file));
        }

        $content = @file_get_contents($file);
        if ($content === false) {
            throw new \RuntimeException(sprintf('Cannot read "%s" file content.', $file));
        }

        return $this->serializer->unserialize($content);
    }
}
