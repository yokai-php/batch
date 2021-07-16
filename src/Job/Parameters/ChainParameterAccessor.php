<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Parameters;

use Yokai\Batch\Exception\CannotAccessParameterException;
use Yokai\Batch\JobExecution;

/**
 * This job parameter accessor implementation iterates over a list of other accessors
 * and return the value from the first successful of the list.
 */
final class ChainParameterAccessor implements JobParameterAccessorInterface
{
    /**
     * @var JobParameterAccessorInterface[]
     * @phpstan-var iterable<JobParameterAccessorInterface>
     */
    private iterable $accessors;

    /**
     * @param JobParameterAccessorInterface[] $accessors
     * @phpstan-param iterable<JobParameterAccessorInterface> $accessors
     */
    public function __construct(iterable $accessors)
    {
        $this->accessors = $accessors;
    }

    /**
     * @inheritdoc
     */
    public function get(JobExecution $execution)
    {
        $tries = [];
        foreach ($this->accessors as $accessor) {
            try {
                return $accessor->get($execution);
            } catch (CannotAccessParameterException $exception) {
                $tries[] = $exception->getMessage();
            }
        }

        throw new CannotAccessParameterException(
            \sprintf(
                'Cannot access parameter, tried using %d accessor(s), all failed : "%s".',
                \count($tries),
                \implode('". "', $tries)
            )
        );
    }
}
