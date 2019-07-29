<?php declare(strict_types=1);

namespace Yokai\Batch\Bridge\Box\Spout;

use Box\Spout\Common\Entity\Row;
use Box\Spout\Reader\Common\Creator\ReaderFactory;
use Box\Spout\Reader\CSV\Reader as CsvReader;
use Box\Spout\Reader\SheetInterface;
use Yokai\Batch\Job\Item\ItemReaderInterface;
use Yokai\Batch\Job\JobExecutionAwareInterface;
use Yokai\Batch\Job\JobExecutionAwareTrait;

final class FlatFileReader implements
    ItemReaderInterface,
    JobExecutionAwareInterface
{
    use JobExecutionAwareTrait;

    public const SOURCE_FILE_PARAMETER = 'sourceFile';

    public const HEADERS_MODE_SKIP = 'skip';
    public const HEADERS_MODE_COMBINE = 'combine';
    public const HEADERS_MODE_NONE = 'none';
    public const AVAILABLE_HEADERS_MODES = [
        self::HEADERS_MODE_SKIP,
        self::HEADERS_MODE_COMBINE,
        self::HEADERS_MODE_NONE,
    ];

    /**
     * @var string
     */
    private $type;

    /**
     * @var array
     */
    private $options;

    /**
     * @var string
     */
    private $headersMode;

    /**
     * @var array|null
     */
    private $headers;

    /**
     * @var string|null
     */
    private $filePath;

    public function __construct(
        string $type,
        array $options = [],
        string $headersMode = self::HEADERS_MODE_NONE,
        array $headers = null,
        string $filePath = null
    ) {
        if (!in_array($headersMode, self::AVAILABLE_HEADERS_MODES, true)) {
            throw new \LogicException(
                sprintf(
                    '"%s" is not a valid header mode. Expecting one of "%s"',
                    $headersMode,
                    implode('", "', self::AVAILABLE_HEADERS_MODES)
                )
            );
        }
        if ($headers !== null && $headersMode === self::HEADERS_MODE_COMBINE) {
            throw new \LogicException(
                sprintf('In "%s" header mode you should not provide header by yourself', self::HEADERS_MODE_COMBINE)
            );
        }

        $this->type = $type;
        $this->options = $options;
        $this->headersMode = $headersMode;
        $this->headers = $headers;
        $this->filePath = $filePath;
    }

    /**
     * @inheritDoc
     */
    public function read(): iterable
    {
        $reader = ReaderFactory::createFromType($this->type);
        if ($reader instanceof CsvReader && isset($this->options['delimiter'])) {
            $reader->setFieldDelimiter($this->options['delimiter']);
        }
        $reader->open($this->getFilePath());

        $headers = $this->headers;

        /** @var SheetInterface $sheet */
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                /** @var Row $row */
                if ($rowIndex === 1) {
                    if ($this->headersMode === self::HEADERS_MODE_COMBINE) {
                        $headers = $row->toArray();
                    }
                    if (in_array($this->headersMode, [self::HEADERS_MODE_COMBINE, self::HEADERS_MODE_SKIP])) {
                        continue;
                    }
                }

                if (is_array($headers)) {
                    $row = array_combine($headers, $row->toArray());
                }

                yield $row;
            }
        }

        $reader->close();
    }

    protected function getFilePath(): string
    {
        return $this->filePath ?: (string) $this->jobExecution->getParameter(self::SOURCE_FILE_PARAMETER);
    }
}
