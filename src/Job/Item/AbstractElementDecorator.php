<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Item;

use Yokai\Batch\Job\JobExecutionAwareInterface;
use Yokai\Batch\Job\JobExecutionAwareTrait;
use Yokai\Batch\Job\JobParametersAwareInterface;
use Yokai\Batch\Job\SummaryAwareInterface;

/**
 * Extends this class if you want to create a decorator of :
 * - {@see ItemReaderInterface}
 * - {@see ItemProcessorInterface}
 * - {@see ItemWriterInterface}
 *
 * This class covers interfaces that the decorated instance might have :
 * - {@see InitializableInterface}
 * - {@see FlushableInterface}
 * - {@see JobExecutionAwareInterface}
 * - {@see SummaryAwareInterface}
 * - {@see JobParametersAwareInterface}
 */
abstract class AbstractElementDecorator implements
    InitializableInterface,
    FlushableInterface,
    JobExecutionAwareInterface
{
    use ElementConfiguratorTrait;
    use JobExecutionAwareTrait;

    public function initialize(): void
    {
        foreach ($this->getDecoratedElements() as $element) {
            $this->configureElementJobContext($element, $this->jobExecution);
            $this->initializeElement($element);
        }
    }

    public function flush(): void
    {
        foreach ($this->getDecoratedElements() as $element) {
            $this->flushElement($element);
        }
    }

    /**
     * Implement this method and return all elements that your class is decorating.
     *
     * @phpstan-return iterable<object>
     */
    abstract protected function getDecoratedElements(): iterable;
}
