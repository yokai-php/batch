<?php

declare(strict_types=1);

namespace Yokai\Batch\Test\Job\Item;

use Yokai\Batch\Job\Item\ElementConfiguratorTrait;
use Yokai\Batch\Job\Item\FlushableInterface;
use Yokai\Batch\Job\Item\InitializableInterface;
use Yokai\Batch\Job\JobExecutionAwareInterface;
use Yokai\Batch\Job\JobExecutionAwareTrait;

abstract class TestDebugComponent implements
    InitializableInterface,
    FlushableInterface,
    JobExecutionAwareInterface
{
    use ElementConfiguratorTrait;
    use JobExecutionAwareTrait;

    private object $decorated;
    private bool $initialized = false;
    private bool $flushed = false;

    public function __construct(object $decorated)
    {
        $this->decorated = $decorated;
    }

    public function initialize(): void
    {
        $this->initialized = true;
        $this->initializeElement($this->decorated);
        $this->configureElementJobContext($this->decorated, $this->jobExecution);
    }

    public function wasInitialized(): bool
    {
        return $this->initialized;
    }

    public function flush(): void
    {
        $this->flushed = true;
        $this->flushElement($this->decorated);
    }

    public function wasFlushed(): bool
    {
        return $this->flushed;
    }
}
