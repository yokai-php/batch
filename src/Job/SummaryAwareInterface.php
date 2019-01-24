<?php declare(strict_types=1);

namespace Yokai\Batch\Job;

use Yokai\Batch\Summary;

interface SummaryAwareInterface
{
    /**
     * @param Summary $summary
     */
    public function setSummary(Summary $summary): void;
}
