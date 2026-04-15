<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests\Dummy;

use Psr\EventDispatcher\EventDispatcherInterface;

final class DebugEventDispatcher implements EventDispatcherInterface
{
    /**
     * @var object[]
     */
    private array $events = [];

    /**
     * @var array<string, callable[]>
     */
    private array $listeners = [];

    public function dispatch(object $event): void
    {
        $this->events[] = $event;
        foreach ($this->listeners[$event::class] ?? [] as $listener) {
            $listener($event);
        }
    }

    public function addListener(string $event, callable $listener): void
    {
        $this->listeners[$event] ??= [];
        $this->listeners[$event][] = $listener;
    }

    /**
     * @return object[]
     */
    public function getEvents(): array
    {
        return $this->events;
    }
}
