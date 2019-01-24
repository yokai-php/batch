<?php declare(strict_types=1);

namespace Yokai\Batch\Bridge\Box\Spout;

use Box\Spout\Writer\WriterFactory;
use Box\Spout\Writer\WriterInterface;
use Yokai\Batch\Job\Item\FlushableInterface;
use Yokai\Batch\Job\Item\InitializableInterface;
use Yokai\Batch\Job\Item\ItemWriterInterface;
use Yokai\Batch\Job\JobExecutionAwareInterface;
use Yokai\Batch\Job\JobExecutionAwareTrait;

final class FlatFileWriter implements
    ItemWriterInterface,
    JobExecutionAwareInterface,
    InitializableInterface,
    FlushableInterface
{
    use JobExecutionAwareTrait;

    public const OUTPUT_FILE_PARAMETER = 'outputFile';

    /**
     * @var string
     */
    private $type;

    /**
     * @var array|null
     */
    private $headers;

    /**
     * @var WriterInterface|null
     */
    private $writer;

    /**
     * @var bool
     */
    private $headersAdded = false;

    /**
     * @var string|null
     */
    private $filePath;

    public function __construct(string $type, array $headers = null, string $filePath = null)
    {
        $this->type = $type;
        $this->headers = $headers;
        $this->filePath = $filePath;
    }

    /**
     * @inheritDoc
     */
    public function initialize(): void
    {
        $this->writer = WriterFactory::create($this->type);
        $this->writer->openToFile($this->getFilePath());
    }

    /**
     * @inheritDoc
     */
    public function write(iterable $items): void
    {
        if (!$this->headersAdded) {
            $this->headersAdded = true;
            if ($this->headers !== null) {
                $this->writer->addRow($this->headers);
            }
        }

        foreach ($items as $row) {
            if (!is_array($row)) {
                throw new \RuntimeException();//todo
            }
            $this->writer->addRow($row);
        }
    }

    /**
     * @inheritDoc
     */
    public function flush(): void
    {
        $this->writer->close();
        $this->writer = null;
        $this->headersAdded = false;
    }

    protected function getFilePath(): string
    {
        return $this->filePath ?: (string)$this->jobExecution->getParameter(self::OUTPUT_FILE_PARAMETER);
    }
}
