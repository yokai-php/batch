<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Item\Writer;

use Yokai\Batch\Exception\UnexpectedValueException;
use Yokai\Batch\Job\Item\ElementConfiguratorTrait;
use Yokai\Batch\Job\Item\Exception\SkipItemException;
use Yokai\Batch\Job\Item\FlushableInterface;
use Yokai\Batch\Job\Item\InitializableInterface;
use Yokai\Batch\Job\Item\ItemProcessorInterface;
use Yokai\Batch\Job\Item\ItemWriterInterface;
use Yokai\Batch\Job\JobExecutionAwareInterface;
use Yokai\Batch\Job\JobExecutionAwareTrait;

/**
 * This {@see ItemWriterInterface} will transform provided items
 * using a {@see ItemProcessorInterface},
 * then will delegate writing to another {@see ItemWriterInterface}.
 */
final class TransformingWriter implements
    ItemWriterInterface,
    JobExecutionAwareInterface,
    InitializableInterface,
    FlushableInterface
{
    use JobExecutionAwareTrait;
    use ElementConfiguratorTrait;

    public function __construct(
        private ItemProcessorInterface $processor,
        private ItemWriterInterface $writer,
    ) {
    }

    public function write(iterable $items): void
    {
        $transformedItems = [];

        foreach ($items as $index => $item) {
            if (!\is_string($index) && !\is_int($index)) {
                throw UnexpectedValueException::type('string|int', $index);
            }

            try {
                $transformedItems[] = $this->processor->process($item);
            } catch (SkipItemException $exception) {
                $this->jobExecution->getLogger()->debug(
                    \sprintf('Skipping item in writer transformation %s.', $index),
                    $exception->getContext() + ['item' => $exception->getItem()]
                );

                $cause = $exception->getCause();
                if ($cause) {
                    $cause->report($this->jobExecution, $index, $exception->getItem());
                }

                continue;
            }
        }

        if (count($transformedItems) > 0) {
            $this->writer->write($transformedItems);
        }
    }

    public function initialize(): void
    {
        $this->configureElementJobContext($this->processor, $this->jobExecution);
        $this->initializeElement($this->processor);
        $this->configureElementJobContext($this->writer, $this->jobExecution);
        $this->initializeElement($this->writer);
    }

    public function flush(): void
    {
        $this->flushElement($this->processor);
        $this->flushElement($this->writer);
    }
}
