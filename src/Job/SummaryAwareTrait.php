<?php

declare(strict_types=1);

namespace Yokai\Batch\Job;

use Yokai\Batch\Summary;

/**
 * Covers {@see SummaryAwareInterface}.
 */
trait SummaryAwareTrait
{
    private Summary $summary;

    public function setSummary(Summary $summary): void
    {
        $this->summary = $summary;
    }
}
