<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Item\Writer;

use Yokai\Batch\Job\Item\ElementConfiguratorTrait;
use Yokai\Batch\Job\Item\FlushableInterface;
use Yokai\Batch\Job\Item\InitializableInterface;
use Yokai\Batch\Job\Item\ItemWriterInterface;
use Closure;
use Yokai\Batch\Job\JobExecutionAwareInterface;
use Yokai\Batch\Job\JobExecutionAwareTrait;

/**
 * This {@see ItemWriterInterface} will transfer writing
 * to another {@see ItemWriterInterface},
 * if the closure you provided tells to.
 */
final class ConditionalWriter implements
    ItemWriterInterface,
    JobExecutionAwareInterface,
    InitializableInterface,
    FlushableInterface
{
    use JobExecutionAwareTrait;
    use ElementConfiguratorTrait;

    private bool $initialized = false;

    public function __construct(
        private Closure $shouldWrite,
        private ItemWriterInterface $writer,
    ) {
    }

    public function write(iterable $items): void
    {
        $keptItems = [];
        foreach ($items as $item) {
            if (($this->shouldWrite)($item, $this->jobExecution)) {
                $keptItems[] = $item;
            }
        }

        if ($keptItems === []) {
            return;
        }

        if (!$this->initialized) {
            $this->configureElementJobContext($this->writer, $this->jobExecution);
            $this->initializeElement($this->writer);
            $this->initialized = true;
        }

        $this->writer->write($keptItems);
    }

    public function initialize(): void
    {
        $this->initialized = false;
    }

    public function flush(): void
    {
        if ($this->initialized) {
            $this->flushElement($this->writer);
        }
    }
}
