<?php declare(strict_types=1);

namespace Yokai\Batch\Storage;

use Throwable;
use Yokai\Batch\Exception\CannotStoreJobExecutionException;
use Yokai\Batch\Exception\JobExecutionNotFoundException;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Serializer\JobExecutionSerializerInterface;

final class FilesystemJobExecutionStorage implements ListableJobExecutionStorageInterface
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
