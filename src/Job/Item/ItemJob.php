<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Item;

use Yokai\Batch\Job\AbstractJob;
use Yokai\Batch\Job\Item\Exception\SkipItemException;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Storage\JobExecutionStorageInterface;

/**
 * This job is at the very center of batch processing.
 * It is built on an ETL (@link https://en.wikipedia.org/wiki/Extract,_transform,_load)
 * architecture, with decoupled and reusable components.
 *
 * Items are Extracted using an {@see ItemReaderInterface}.
 * Then Transformed using an {@see ItemProcessorInterface}.
 * And finally Loaded using an {@see ItemWriterInterface}.
 */
class ItemJob extends AbstractJob
{
    use ElementConfiguratorTrait;

    private int $batchSize;
    private ItemReaderInterface $reader;
    private ItemProcessorInterface $processor;
    private ItemWriterInterface $writer;
    private JobExecutionStorageInterface $executionStorage;

    /**
     * @phpstan-var list<object>
     */
    private array $elements;

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
        $logger = $jobExecution->getLogger();

        $this->initializeElements($jobExecution);

        $writeCount = 0;
        $itemsToWrite = [];
        foreach ($this->reader->read() as $readIndex => $readItem) {
            $summary->increment('read');

            try {
                $processedItem = $this->processor->process($readItem);
            } catch (SkipItemException $exception) {
                $summary->increment('skipped');
                $logger->debug(
                    \sprintf('Skipping item %s.', $readIndex),
                    $exception->getContext() + ['item' => $exception->getItem()]
                );

                $cause = $exception->getCause();
                if ($cause) {
                    $cause->report($jobExecution, $readIndex, $exception->getItem());
                }

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
