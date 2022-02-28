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

    /**
     * @phpstan-var list<object>
     */
    private array $elements;

    public function __construct(
        private int $batchSize,
        private ItemReaderInterface $reader,
        private ItemProcessorInterface $processor,
        private ItemWriterInterface $writer,
        private JobExecutionStorageInterface $executionStorage,
    ) {
        $this->elements = [$reader, $processor, $writer];
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
        /** @var int|string $readIndex */
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
     * @return iterable<mixed>
     */
    protected function getItemsToWrite(mixed $processedItem): iterable
    {
        if ($processedItem instanceof ExpandProcessedItem) {
            return $processedItem;
        }

        return [$processedItem];
    }

    /**
     * Set up elements before execution.
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
