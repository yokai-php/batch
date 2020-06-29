<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Item\Processor;

use Yokai\Batch\Job\Item\ElementConfiguratorTrait;
use Yokai\Batch\Job\Item\FlushableInterface;
use Yokai\Batch\Job\Item\InitializableInterface;
use Yokai\Batch\Job\Item\ItemProcessorInterface;
use Yokai\Batch\Job\JobExecutionAwareInterface;
use Yokai\Batch\Job\JobExecutionAwareTrait;

final class ChainProcessor implements
    ItemProcessorInterface,
    InitializableInterface,
    FlushableInterface,
    JobExecutionAwareInterface
{
    use ElementConfiguratorTrait;
    use JobExecutionAwareTrait;

    /**
     * @var iterable|ItemProcessorInterface[]
     */
    private $processors;

    /**
     * @param iterable|ItemProcessorInterface[] $processors
     */
    public function __construct(iterable $processors)
    {
        $this->processors = $processors;
    }

    /**
     * @inheritDoc
     */
    public function initialize(): void
    {
        foreach ($this->processors as $processor) {
            $this->configureElementJobContext($processor, $this->jobExecution);
            $this->initializeElement($processor);
        }
    }

    /**
     * @inheritDoc
     */
    public function process($item)
    {
        foreach ($this->processors as $processor) {
            $item = $processor->process($item);
        }

        return $item;
    }

    /**
     * @inheritDoc
     */
    public function flush(): void
    {
        foreach ($this->processors as $processor) {
            $this->flushElement($processor);
        }
    }
}
