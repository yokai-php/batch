<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Item\Reader;

use Yokai\Batch\Job\Item\ItemReaderInterface;
use Yokai\Batch\Job\JobExecutionAwareInterface;
use Yokai\Batch\Job\JobExecutionAwareTrait;
use Yokai\Batch\Job\Parameters\JobParameterAccessorInterface;

/**
 * This {@see ItemReaderInterface} uses a {@see JobParameterAccessorInterface} to read data from.
 */
final class ParameterAccessorReader implements ItemReaderInterface, JobExecutionAwareInterface
{
    use JobExecutionAwareTrait;

    public function __construct(
        private JobParameterAccessorInterface $data,
    ) {
    }

    /**
     * @inheritdoc
     */
    public function read(): iterable
    {
        $data = $this->data->get($this->jobExecution);
        if (\is_iterable($data)) {
            return $data;
        }

        return [$data];
    }
}
