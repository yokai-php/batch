<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Item;

use Yokai\Batch\Job\JobExecutionAwareInterface;
use Yokai\Batch\Job\JobParametersAwareInterface;
use Yokai\Batch\Job\SummaryAwareInterface;
use Yokai\Batch\JobExecution;

/**
 * Use this trait whenever you have objects that might implement :
 * - {@see JobExecutionAwareInterface}
 * - {@see JobParametersAwareInterface}
 * - {@see SummaryAwareInterface}
 * - {@see InitializableInterface}
 * - {@see FlushableInterface}
 */
trait ElementConfiguratorTrait
{
    private function configureElementJobContext(object $element, JobExecution $jobExecution): void
    {
        if ($element instanceof JobExecutionAwareInterface) {
            $element->setJobExecution($jobExecution);
        }
        if ($element instanceof JobParametersAwareInterface) {
            $element->setJobParameters($jobExecution->getParameters());
        }
        if ($element instanceof SummaryAwareInterface) {
            $element->setSummary($jobExecution->getSummary());
        }
    }

    private function initializeElement(object $element): void
    {
        if ($element instanceof InitializableInterface) {
            $element->initialize();
        }
    }

    private function flushElement(object $element): void
    {
        if ($element instanceof FlushableInterface) {
            $element->flush();
        }
    }
}
