<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Item\Writer\Filesystem;

use Yokai\Batch\Exception\RuntimeException;
use Yokai\Batch\Job\Item\FlushableInterface;
use Yokai\Batch\Job\Item\InitializableInterface;
use Yokai\Batch\Job\Item\ItemWriterInterface;
use Yokai\Batch\Job\JobExecutionAwareInterface;
use Yokai\Batch\Job\JobExecutionAwareTrait;
use Yokai\Batch\Job\Parameters\JobParameterAccessorInterface;

/**
 * This {@see ItemWriterInterface} writes each item as a JSON string to a file.
 * @link https://jsonlines.org/
 */
final class JsonLinesWriter implements
    ItemWriterInterface,
    InitializableInterface,
    FlushableInterface,
    JobExecutionAwareInterface
{
    use JobExecutionAwareTrait;

    private JobParameterAccessorInterface $filePath;

    /**
     * @var resource
     */
    private $file;

    public function __construct(JobParameterAccessorInterface $filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * @inheritdoc
     */
    public function initialize(): void
    {
        $path = (string)$this->filePath->get($this->jobExecution);
        $dir = \dirname($path);
        if (!@\is_dir($dir) && !@\mkdir($dir, 0777, true)) {
            throw new RuntimeException(
                \sprintf('Cannot create dir "%s".', $dir)
            );
        }

        $file = @\fopen($path, 'w+');
        if ($file === false) {
            throw new RuntimeException(\sprintf('Cannot open %s for writing.', $path));
        }

        $this->file = $file;
    }

    /**
     * @inheritdoc
     */
    public function write(iterable $items): void
    {
        foreach ($items as $json) {
            if (!\is_string($json)) {
                $json = \json_encode($json);
            }
            \fwrite($this->file, $json . \PHP_EOL);
        }
    }

    /**
     * @inheritdoc
     */
    public function flush(): void
    {
        if (isset($this->file)) {
            \fclose($this->file);
        }
    }
}
