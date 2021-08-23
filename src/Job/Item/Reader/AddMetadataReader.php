<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Item\Reader;

use Generator;
use Yokai\Batch\Exception\UnexpectedValueException;
use Yokai\Batch\Job\Item\ElementConfiguratorTrait;
use Yokai\Batch\Job\Item\FlushableInterface;
use Yokai\Batch\Job\Item\InitializableInterface;
use Yokai\Batch\Job\Item\ItemReaderInterface;
use Yokai\Batch\Job\JobExecutionAwareInterface;
use Yokai\Batch\Job\JobExecutionAwareTrait;

final class AddMetadataReader implements
    ItemReaderInterface,
    InitializableInterface,
    FlushableInterface,
    JobExecutionAwareInterface
{
    use ElementConfiguratorTrait;
    use JobExecutionAwareTrait;

    private ItemReaderInterface $reader;

    /**
     * @phpstan-var array<string, mixed>
     */
    private array $metadata;

    /**
     * @phpstan-param array<string, mixed> $metadata
     */
    public function __construct(ItemReaderInterface $reader, array $metadata)
    {
        $this->reader = $reader;
        $this->metadata = $metadata;
    }

    /**
     * @inheritDoc
     */
    public function initialize(): void
    {
        $this->configureElementJobContext($this->reader, $this->jobExecution);
        $this->initializeElement($this->reader);
    }

    /**
     * @inheritdoc
     * @phpstan-return Generator<array<mixed>>
     */
    public function read(): Generator
    {
        foreach ($this->reader->read() as $item) {
            if (!\is_array($item)) {
                throw UnexpectedValueException::type('array', $item);
            }

            yield $this->metadata + $item;
        }
    }

    /**
     * @inheritDoc
     */
    public function flush(): void
    {
        $this->flushElement($this->reader);
    }
}
