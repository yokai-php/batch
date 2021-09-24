<?php

declare(strict_types=1);

namespace Yokai\Batch\Event;

use Yokai\Batch\JobExecution;

class JobEvent
{
    private JobExecution $execution;

    public function __construct(JobExecution $execution)
    {
        $this->execution = $execution;
    }

    public function getExecution(): JobExecution
    {
        return $this->execution;
    }
}
