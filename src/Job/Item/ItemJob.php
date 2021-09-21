<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Item;

use Yokai\Batch\Job\AbstractJob;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Storage\JobExecutionStorageInterface;
use Yokai\Batch\Warning;

class ItemJob extends AbstractJob
{
    use ElementConfiguratorTrait;

    /**
     * @var int
     */
    private int $batchSize;

    /**
     * @var ItemReaderInterface
     */
    private ItemReaderInterface $reader;

    /**
     * @var ItemProcessorInterface
     */
    private ItemProcessorInterface $processor;

    /**
     * @var ItemWriterInterface
     */
    private ItemWriterInterface $writer;

    /**
     * @phpstan-var list<object>
     */
    private array $elements;

    /**
     * @var JobExecutionStorageInterface
     */
    private JobExecutionStorageInterface $executionStorage;

    /**
     * @param int                          $batchSize
     * @param ItemReaderInterface          $reader
     * @param ItemProcessorInterface       $processor
     * @param ItemWriterInterface          $writer
     * @param JobExecutionStorageInterface $executionStorage
     */
    public function __construct(
        int $batchSize,
        ItemReaderInterface $reader,
        ItemProcessorInterface $processor,
        ItemWriterInterface $writer,
        JobExecutionStorageInterface $executionStorage
    ) {
        $this->batchSize = $batchSize;
        $this->reader = $reader;
        $this->processor = $processor;
        $this->writer = $writer;
        $this->elements = [$reader, $processor, $writer];
        $this->executionStorage = $executionStorage;
    }

    /**
     * @inheritDoc
     */
    final protected function doExecute(JobExecution $jobExecution): void
    {
        $rootExecution = $jobExecution->getRootExecution();
        $summary = $jobExecution->getSummary();

        $this->initializeElements($jobExecution);

        $writeCount = 0;
        $itemsToWrite = [];
        foreach ($this->reader->read() as $readIndex => $readItem) {
            $summary->increment('read');

            try {
                $processedItem = $this->processor->process($readItem);
            } catch (InvalidItemException $exception) {
                $summary->increment('invalid');
                $jobExecution->addWarning(
                    new Warning($exception->getMessage(), $exception->getParameters(), ['itemIndex' => $readIndex])
                );

                continue;
            }

            $summary->increment('processed');

            foreach ($this->getItemsToWrite($processedItem) as $item) {
                $itemsToWrite[] = $item;
                $writeCount++;

                if (0 === $writeCount % $this->batchSize) {
                    $this->writer->write($itemsToWrite);
                    $summary->increment('write', $writeCount);
                    $itemsToWrite = [];
                    $writeCount = 0;

                    $this->executionStorage->store($rootExecution);
                }
            }
        }

        if ($writeCount > 0) {
            $this->writer->write($itemsToWrite);
            $summary->increment('write', $writeCount);

            $this->executionStorage->store($rootExecution);
        }

        $this->flushElements();
    }

    /**
     * Analyse processed item to determine the items to write.
     *
     * @param mixed $processedItem The processed item
     *
     * @return iterable A list of items to write
     *
     * @phpstan-return iterable<mixed>
     */
    protected function getItemsToWrite($processedItem): iterable
    {
        if ($processedItem instanceof ExpandProcessedItem) {
            return $processedItem;
        }

        return [$processedItem];
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
