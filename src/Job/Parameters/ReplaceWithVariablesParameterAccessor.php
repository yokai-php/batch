<?php

declare(strict_types=1);

namespace Yokai\Batch\Job\Parameters;

use DateTimeImmutable;
use Yokai\Batch\JobExecution;

/**
 * This job parameter accessor implementation is decorating another accessor
 * and replaces some variables from returned value (if it is a string).
 */
class ReplaceWithVariablesParameterAccessor implements JobParameterAccessorInterface
{
    public function __construct(
        private JobParameterAccessorInterface $accessor,
        /**
         * @phpstan-var array<string, string>
         */
        private array $variables = []
    ) {
    }

    /**
     * @inheritdoc
     */
    public function get(JobExecution $execution): mixed
    {
        $parameter = $this->accessor->get($execution);
        if (!\is_string($parameter)) {
            return $parameter;
        }

        return \strtr($parameter, $this->variables + $this->getVariables($execution));
    }

    /**
     * @phpstan-return array<string, string>
     */
    protected function getVariables(JobExecution $execution): array
    {
        return [
            '{job}' => $execution->getJobName(),
            '{id}' => $execution->getId(),
            '{date}' => ($execution->getStartTime() ?: new DateTimeImmutable())->format('YmdHis'),
            '{seed}' => \uniqid(),
        ];
    }
}
