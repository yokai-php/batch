<?php declare(strict_types=1);

namespace Yokai\Batch\Job;

use Yokai\Batch\Summary;

trait SummaryAwareTrait
{
    /**
     * @var Summary
     */
    private $summary;

    /**
     * @param Summary $summary
     */
    public function setSummary(Summary $summary): void
    {
        $this->summary = $summary;
    }
}
