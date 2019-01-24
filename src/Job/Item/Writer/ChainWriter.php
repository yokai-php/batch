<?php declare(strict_types=1);

namespace Yokai\Batch\Job\Item\Writer;

use Yokai\Batch\Job\Item\ElementConfiguratorTrait;
use Yokai\Batch\Job\Item\FlushableInterface;
use Yokai\Batch\Job\Item\InitializableInterface;
use Yokai\Batch\Job\Item\ItemWriterInterface;
use Yokai\Batch\Job\JobExecutionAwareInterface;
use Yokai\Batch\Job\JobExecutionAwareTrait;

final class ChainWriter implements ItemWriterInterface,
    InitializableInterface,
    FlushableInterface,
    JobExecutionAwareInterface
{
    use ElementConfiguratorTrait,
        JobExecutionAwareTrait;

    /**
     * @var iterable|ItemWriterInterface[]
     */
    private $writers;

    /**
     * @param iterable|ItemWriterInterface[] $writers
     */
    public function __construct(iterable $writers)
    {
        $this->writers = $writers;
    }

    /**
     * @inheritDoc
     */
    public function initialize(): void
    {
        foreach ($this->writers as $writer) {
            $this->configureElementJobContext($writer, $this->jobExecution);
            $this->initializeElement($writer);
        }
    }

    /**
     * @inheritDoc
     */
    public function write(iterable $items): void
    {
        foreach ($this->writers as $writer) {
            $writer->write($items);
        }
    }

    /**
     * @inheritDoc
     */
    public function flush(): void
    {
        foreach ($this->writers as $writer) {
            $this->flushElement($writer);
        }
    }
}
