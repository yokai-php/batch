<?php

declare(strict_types=1);

namespace Yokai\Batch\Event;

use Yokai\Batch\JobExecution;

class JobEvent
{
    public function __construct(
        private JobExecution $execution,
    ) {
    }

    public function getExecution(): JobExecution
    {
        return $this->execution;
    }
}
