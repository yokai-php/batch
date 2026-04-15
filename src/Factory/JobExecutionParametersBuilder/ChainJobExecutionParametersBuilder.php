<?php

declare(strict_types=1);

namespace Yokai\Batch\Factory\JobExecutionParametersBuilder;

use Yokai\Batch\Factory\JobExecutionParametersBuilderInterface;

/**
 * This {@see JobExecutionParametersBuilderInterface} is using multiple {@see JobExecutionParametersBuilderInterface}
 * implementations, calls each and merge the results into a single array.
 * The later the {@see JobExecutionParametersBuilderInterface} is in the list, the more chance it's values will be kept.
 */
final readonly class ChainJobExecutionParametersBuilder implements JobExecutionParametersBuilderInterface
{
    public function __construct(
        /**
         * @var iterable<JobExecutionParametersBuilderInterface>
         */
        private iterable $builders,
    ) {
    }

    public function build(string $name): array
    {
        $values = [];
        foreach ($this->builders as $builder) {
            $values[] = $builder->build($name);
        }

        if ($values === []) {
            return [];
        }

        return \array_merge(...$values);
    }
}
