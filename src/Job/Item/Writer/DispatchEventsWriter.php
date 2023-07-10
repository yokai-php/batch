<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Item\Writer;

use Psr\EventDispatcher\EventDispatcherInterface;
use Yokai\Batch\Event\PostWriteEvent;
use Yokai\Batch\Event\PreWriteEvent;
use Yokai\Batch\Job\Item\AbstractElementDecorator;
use Yokai\Batch\Job\Item\ItemWriterInterface;

/**
 * This {@see ItemWriterInterface} act as decorator,
 * and will dispatch {@see PreWriteEvent} before, and {@see PostWriteEvent} after.
 */
final class DispatchEventsWriter extends AbstractElementDecorator implements ItemWriterInterface
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private ItemWriterInterface $writer,
    ) {
    }

    public function write(iterable $items): void
    {
        $this->dispatch(new PreWriteEvent($this->getJobExecution()));

        $this->writer->write($items);

        $this->dispatch(new PostWriteEvent($this->getJobExecution()));
    }

    protected function getDecoratedElements(): iterable
    {
        return [$this->writer];
    }

    private function dispatch(object $event): void
    {
        try {
            $this->eventDispatcher->dispatch($event);
        } catch (\Throwable $error) {
            $this->getJobExecution()->getLogger()->error(
                'An error occurred while dispatching event.',
                ['event' => $event::class, 'error' => (string)$error],
            );
        }
    }
}
