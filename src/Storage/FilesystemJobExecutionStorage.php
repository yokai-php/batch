<?php declare(strict_types=1);

namespace Yokai\Batch\Storage;

use Throwable;
use Yokai\Batch\Exception\CannotStoreJobExecutionException;
use Yokai\Batch\Exception\JobExecutionNotFoundException;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Serializer\JobExecutionSerializerInterface;

final class FilesystemJobExecutionStorage implements JobExecutionStorageInterface
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
        $path = $this->buildFilePath($execution->getJobName(), $execution->getId());
        if (file_exists($path)) {
            throw new CannotStoreJobExecutionException(
                $execution->getJobName(),
                $execution->getId(),
                new \RuntimeException(sprintf('File "%s" exists already.', $path))
            );
        }

        $dir = dirname($path);
        if (!is_dir($dir) && false === @mkdir($dir, 0777, true)) {
            throw new CannotStoreJobExecutionException(
                $execution->getJobName(),
                $execution->getId(),
                new \RuntimeException(sprintf('Cannot create dir "%s".', $path))
            );
        }

        try {
            $content = $this->serializer->serialize($execution);
        } catch (Throwable $exception) {
            throw new CannotStoreJobExecutionException($execution->getJobName(), $execution->getId(), $exception);
        }

        if (false === file_put_contents($path, $content)) {
            throw new CannotStoreJobExecutionException(
                $execution->getJobName(),
                $execution->getId(),
                new \RuntimeException(sprintf('Cannot write content to file "%s".', $path))
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function retrieve(string $jobInstanceName, string $id): JobExecution
    {
        $path = $this->buildFilePath($jobInstanceName, $id);
        if (!file_exists($path)) {
            throw new JobExecutionNotFoundException(
                $jobInstanceName,
                $id,
                new \RuntimeException(sprintf('File "%s" does not exists.', $path))
            );
        }

        $content = @file_get_contents($path);
        if ($content === false) {
            throw new JobExecutionNotFoundException(
                $jobInstanceName,
                $id,
                new \RuntimeException(sprintf('Cannot read "%s" file content.', $path))
            );
        }

        try {
            return $this->serializer->unserialize($content);
        } catch (Throwable $exception) {
            throw new JobExecutionNotFoundException($jobInstanceName, $id, $exception);
        }
    }

    /**
     * @param string $jobInstanceName
     * @param string $id
     *
     * @return string
     */
    public function buildFilePath(string $jobInstanceName, string $id): string
    {
        return implode(DIRECTORY_SEPARATOR, [$this->directory, $jobInstanceName, $id]).'.'.$this->extension;
    }
}
