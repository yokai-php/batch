<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Item\Reader\Filesystem;

use Generator;
use Yokai\Batch\Exception\RuntimeException;
use Yokai\Batch\Exception\UnexpectedValueException;
use Yokai\Batch\Job\Item\ItemReaderInterface;
use Yokai\Batch\Job\JobExecutionAwareInterface;
use Yokai\Batch\Job\JobExecutionAwareTrait;
use Yokai\Batch\Job\Parameters\JobParameterAccessorInterface;

/**
 * This {@see ItemReaderInterface} reads from a file and convert every line to an array.
 * Every line must have fixed size columns,
 * according to the parameters provided as constructor argument.
 */
final class FixedColumnSizeFileReader implements
    ItemReaderInterface,
    JobExecutionAwareInterface
{
    use JobExecutionAwareTrait;

    public const HEADERS_MODE_SKIP = 'skip';
    public const HEADERS_MODE_COMBINE = 'combine';
    public const HEADERS_MODE_NONE = 'none';
    private const AVAILABLE_HEADERS_MODES = [
        self::HEADERS_MODE_SKIP,
        self::HEADERS_MODE_COMBINE,
        self::HEADERS_MODE_NONE,
    ];

    /**
     * @var int[]
     */
    private array $columns;

    private string $headersMode;
    private JobParameterAccessorInterface $filePath;

    /**
     * @param int[] $columns
     */
    public function __construct(
        array $columns,
        JobParameterAccessorInterface $filePath,
        string $headersMode = self::HEADERS_MODE_NONE
    ) {
        if (!\in_array($headersMode, self::AVAILABLE_HEADERS_MODES, true)) {
            throw UnexpectedValueException::enum(self::AVAILABLE_HEADERS_MODES, $headersMode, 'Invalid header mode.');
        }

        $this->columns = $columns;
        $this->headersMode = $headersMode;
        $this->filePath = $filePath;
    }

    /**
     * @inheritdoc
     * @phpstan-return Generator<array<mixed>>
     */
    public function read(): Generator
    {
        /** @var string $path */
        $path = $this->filePath->get($this->jobExecution);
        $handle = @\fopen($path, 'r');
        if ($handle === false) {
            throw new RuntimeException(\sprintf('Cannot read %s.', $path));
        }

        $headers = \array_keys($this->columns);

        $index = -1;

        while (($line = \fgets($handle)) !== false) {
            $index++;

            $start = 0;
            $row = [];
            foreach ($this->columns as $size) {
                $row[] = \trim(\mb_substr($line, $start, $size));
                $start += $size;
            }

            if ($index === 0) {
                if ($this->headersMode === self::HEADERS_MODE_COMBINE) {
                    $headers = $row;
                }
                if (\in_array($this->headersMode, [self::HEADERS_MODE_COMBINE, self::HEADERS_MODE_SKIP], true)) {
                    continue;
                }
            }

            if (\is_array($headers)) {
                $row = \array_combine($headers, $row);
            }

            yield $row;
        }

        \fclose($handle);
    }
}
