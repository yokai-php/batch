<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Item\Writer;

use Yokai\Batch\Exception\UnexpectedValueException;
use Yokai\Batch\Finder\FinderInterface;
use Yokai\Batch\Job\Item\ElementConfiguratorTrait;
use Yokai\Batch\Job\Item\FlushableInterface;
use Yokai\Batch\Job\Item\InitializableInterface;
use Yokai\Batch\Job\Item\ItemWriterInterface;
use Yokai\Batch\Job\JobExecutionAwareInterface;
use Yokai\Batch\Job\JobExecutionAwareTrait;

/**
 * This {@see ItemWriterInterface} calls different writer for items,
 * based on the logic you put in the provided {@see FinderInterface}.
 */
final class RoutingWriter implements
    ItemWriterInterface,
    InitializableInterface,
    FlushableInterface,
    JobExecutionAwareInterface
{
    use ElementConfiguratorTrait;
    use JobExecutionAwareTrait;

    /**
     * @var ItemWriterInterface[]
     */
    private array $writers = [];

    public function __construct(
        /**
         * @phpstan-var FinderInterface<ItemWriterInterface>
         */
        private FinderInterface $finder,
    ) {
    }

    public function write(iterable $items): void
    {
        $writerAndItems = [];
        foreach ($items as $item) {
            $writer = $this->finder->find($item);
            if (!$writer instanceof ItemWriterInterface) {
                throw UnexpectedValueException::type(ItemWriterInterface::class, $writer);
            }

            $writerId = \spl_object_hash($writer);

            if (!isset($this->writers[$writerId])) {
                $this->writers[$writerId] = $writer;
                $this->configureElementJobContext($writer, $this->jobExecution);
                $this->initializeElement($writer);
            }

            $writerAndItems[$writerId] ??= [$writer, []];
            $writerAndItems[$writerId][1][] = $item;
        }

        foreach ($writerAndItems as [$writer, $writerItems]) {
            $writer->write($writerItems);
        }
    }

    public function initialize(): void
    {
        $this->writers = [];
    }

    public function flush(): void
    {
        $writers = $this->writers;
        $this->writers = [];

        foreach ($writers as $writer) {
            $this->flushElement($writer);
        }
    }
}
