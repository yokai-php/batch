<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Item\Reader;

use Yokai\Batch\Job\Item\ElementConfiguratorTrait;
use Yokai\Batch\Job\Item\FlushableInterface;
use Yokai\Batch\Job\Item\InitializableInterface;
use Yokai\Batch\Job\Item\ItemReaderInterface;
use Yokai\Batch\Job\JobExecutionAwareInterface;
use Yokai\Batch\Job\JobExecutionAwareTrait;

final class SequenceReader implements
    ItemReaderInterface,
    InitializableInterface,
    FlushableInterface,
    JobExecutionAwareInterface
{
    use ElementConfiguratorTrait;
    use JobExecutionAwareTrait;

    /**
     * @var iterable|ItemReaderInterface[]
     */
    private $readers;

    /**
     * @param iterable|ItemReaderInterface[] $readers
     */
    public function __construct(iterable $readers)
    {
        $this->readers = $readers;
    }

    /**
     * @inheritDoc
     */
    public function initialize(): void
    {
        foreach ($this->readers as $reader) {
            $this->configureElementJobContext($reader, $this->jobExecution);
            $this->initializeElement($reader);
        }
    }

    /**
     * @inheritDoc
     */
    public function read(): iterable
    {
        foreach ($this->readers as $reader) {
            foreach ($reader->read() as $item) {
                yield $item;
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function flush(): void
    {
        foreach ($this->readers as $reader) {
            $this->flushElement($reader);
        }
    }
}
