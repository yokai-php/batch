<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Item\Reader\Filesystem;

use Generator;
use Yokai\Batch\Exception\RuntimeException;
use Yokai\Batch\Job\Item\ItemReaderInterface;
use Yokai\Batch\Job\JobExecutionAwareInterface;
use Yokai\Batch\Job\JobExecutionAwareTrait;
use Yokai\Batch\Job\Parameters\JobParameterAccessorInterface;

/**
 * This {@see ItemReaderInterface} reads from a file and convert every line to an array.
 * Every line must be a valid JSON value (accepted by {@see json_decode} function).
 * @link https://jsonlines.org/
 */
final class JsonLinesReader implements
    ItemReaderInterface,
    JobExecutionAwareInterface
{
    use JobExecutionAwareTrait;

    private JobParameterAccessorInterface $filePath;

    public function __construct(JobParameterAccessorInterface $filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * @inheritdoc
     * @phpstan-return Generator<mixed>
     */
    public function read(): Generator
    {
        $path = (string)$this->filePath->get($this->jobExecution);
        $file = @\fopen($path, 'r');
        if ($file === false) {
            throw new RuntimeException(\sprintf('Cannot open %s for reading.', $path));
        }

        while ($line = \fgets($file)) {
            yield \json_decode($line, true);
        }

        \fclose($file);
    }
}
