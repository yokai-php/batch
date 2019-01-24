<?php declare(strict_types=1);

namespace Yokai\Batch\Job\Item;

use Yokai\Batch\Job\AbstractJob;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Warning;

class ItemJob extends AbstractJob
{
    use ElementConfiguratorTrait;

    /**
     * @var int
     */
    private $batchSize;

    /**
     * @var ItemReaderInterface
     */
    private $reader;

    /**
     * @var ItemProcessorInterface
     */
    private $processor;

    /**
     * @var ItemWriterInterface
     */
    private $writer;

    /**
     * @var array
     */
    private $elements;

    /**
     * @param int                    $batchSize
     * @param ItemReaderInterface    $reader
     * @param ItemProcessorInterface $processor
     * @param ItemWriterInterface    $writer
     */
    public function __construct(
        int $batchSize,
        ItemReaderInterface $reader,
        ItemProcessorInterface $processor,
        ItemWriterInterface $writer
    ) {
        $this->batchSize = $batchSize;
        $this->reader = $reader;
        $this->processor = $processor;
        $this->writer = $writer;
        $this->elements = [$reader, $processor, $writer];
    }

    /**
     * @inheritDoc
     */
    protected function doExecute(JobExecution $jobExecution): void
    {
        $summary = $jobExecution->getSummary();

        $this->initializeElements($jobExecution);

        $writeCount = 0;
        $itemsToWrite = [];
        foreach ($this->reader->read() as $readItem) {
            $summary->increment('read');

            try {
                $processedItem = $this->processor->process($readItem);
            } catch (InvalidItemException $exception) {
                $summary->increment('invalid');
                $jobExecution->addWarning(new Warning($exception->getMessage(), $exception->getParameters()));

                continue;
            }

            $summary->increment('processed');

            $itemsToWrite[] = $processedItem;
            $writeCount++;

            if (0 === $writeCount % $this->batchSize) {
                $this->writer->write($itemsToWrite);
                $summary->increment('write', count($itemsToWrite));
                $itemsToWrite = [];
            }
        }

        if ($writeCount > 0) {
            $this->writer->write($itemsToWrite);
            $summary->increment('write', count($itemsToWrite));
        }

        $this->flushElements();
    }

    /**
     * Set up elements before execution.
     *
     * @param JobExecution $jobExecution
     */
    private function initializeElements(JobExecution $jobExecution): void
    {
        foreach ($this->elements as $element) {
            $this->configureElementJobContext($element, $jobExecution);
            $this->initializeElement($element);
        }
    }

    /**
     * Tear down elements after execution.
     */
    private function flushElements(): void
    {
        foreach ($this->elements as $element) {
            $this->flushElement($element);
        }
    }
}
