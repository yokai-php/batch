<?php

declare(strict_types=1);

namespace Yokai\Batch\Serializer;

use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use Throwable;
use Yokai\Batch\BatchStatus;
use Yokai\Batch\Exception\RuntimeException;
use Yokai\Batch\Exception\UnexpectedValueException;
use Yokai\Batch\Failure;
use Yokai\Batch\JobExecution;
use Yokai\Batch\JobExecutionLogs;
use Yokai\Batch\JobParameters;
use Yokai\Batch\Storage\JobExecutionStorageInterface;
use Yokai\Batch\Summary;
use Yokai\Batch\Warning;

/**
 * This {@see JobExecutionStorageInterface} will (un)serialise any {@see JobExecution} to/from json,
 * using internal (de)normalisation and PHP {@see json_encode} and {@see json_decode} functions.
 */
final class JsonJobExecutionSerializer implements JobExecutionSerializerInterface
{
    public function serialize(JobExecution $jobExecution): string
    {
        try {
            $json = \json_encode($this->toArray($jobExecution));
            if (!\is_string($json) || \json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception(\json_last_error_msg());
            }
        } catch (Throwable $exception) {
            throw RuntimeException::error($exception, 'Cannot serialize job execution to JSON.');
        }

        return $json;
    }

    public function unserialize(string $serializedJobExecution): JobExecution
    {
        try {
            $data = \json_decode($serializedJobExecution, true);
            if (\json_last_error() !== \JSON_ERROR_NONE) {
                throw new Exception(\json_last_error_msg());
            }
            if (!\is_array($data)) {
                throw UnexpectedValueException::type('array', $data);
            }

            return $this->fromArray($data);
        } catch (Throwable $exception) {
            throw RuntimeException::error($exception, 'Cannot unserialize job execution from JSON.');
        }
    }

    public function extension(): string
    {
        return 'json';
    }

    /**
     * @phpstan-return array<string, mixed>
     */
    private function toArray(JobExecution $jobExecution): array
    {
        return [
            'id' => $jobExecution->getId(),
            'jobName' => $jobExecution->getJobName(),
            'status' => $jobExecution->getStatus()->getValue(),
            'parameters' => iterator_to_array($jobExecution->getParameters()),
            'startTime' => $this->dateToString($jobExecution->getStartTime()),
            'endTime' => $this->dateToString($jobExecution->getEndTime()),
            'summary' => $jobExecution->getSummary()->all(),
            'failures' => array_map([$this, 'failureToArray'], $jobExecution->getFailures()),
            'warnings' => array_map([$this, 'warningToArray'], $jobExecution->getWarnings()),
            'childExecutions' => array_map([$this, 'toArray'], $jobExecution->getChildExecutions()),
            'logs' => $jobExecution->getParentExecution() === null ? (string)$jobExecution->getLogs() : '',
        ];
    }

    /**
     * @phpstan-param array<string, mixed> $jobExecutionData
     */
    private function fromArray(array $jobExecutionData, JobExecution $parentExecution = null): JobExecution
    {
        $name = $jobExecutionData['jobName'];
        $status = new BatchStatus($jobExecutionData['status']);
        $parameters = new JobParameters($jobExecutionData['parameters']);
        $summary = new Summary($jobExecutionData['summary']);

        if ($parentExecution !== null) {
            $jobExecution = JobExecution::createChild($parentExecution, $name, $status, $parameters, $summary);
            $parentExecution->addChildExecution($jobExecution);
        } else {
            $jobExecution = JobExecution::createRoot(
                $jobExecutionData['id'],
                $name,
                $status,
                $parameters,
                $summary,
                new JobExecutionLogs($jobExecutionData['logs'] ?? '')
            );
        }

        $jobExecution->setStartTime($this->stringToDate($jobExecutionData['startTime']));
        $jobExecution->setEndTime($this->stringToDate($jobExecutionData['endTime']));

        foreach ($jobExecutionData['failures'] as $failureData) {
            $jobExecution->addFailure($this->failureFromArray($failureData), false);
        }
        foreach ($jobExecutionData['warnings'] as $warningData) {
            $jobExecution->addWarning($this->warningFromArray($warningData), false);
        }

        foreach ($jobExecutionData['childExecutions'] as $childExecutionData) {
            $jobExecution->addChildExecution($this->fromArray($childExecutionData, $jobExecution));
        }

        return $jobExecution;
    }

    private function dateToString(?DateTimeInterface $date): ?string
    {
        if ($date === null) {
            return null;
        }

        return $date->format(DateTimeInterface::ISO8601);
    }

    private function stringToDate(?string $date): ?DateTimeInterface
    {
        if ($date === null) {
            return null;
        }

        $dateObject = DateTimeImmutable::createFromFormat(DateTimeInterface::ISO8601, $date);
        if ($dateObject === false) {
            throw UnexpectedValueException::date(DateTimeInterface::ISO8601, $date);
        }

        return $dateObject;
    }

    /**
     * @phpstan-return array<string, mixed>
     */
    private function failureToArray(Failure $failure): array
    {
        return [
            'class' => $failure->getClass(),
            'message' => $failure->getMessage(),
            'code' => $failure->getCode(),
            'parameters' => $failure->getParameters(),
            'trace' => $failure->getTrace(),
        ];
    }

    /**
     * @phpstan-param array<string, mixed> $array
     */
    private function failureFromArray(array $array): Failure
    {
        return new Failure(
            $array['class'],
            $array['message'],
            $array['code'],
            $array['parameters'],
            $array['trace']
        );
    }

    /**
     * @phpstan-return array<string, mixed>
     */
    private function warningToArray(Warning $warning): array
    {
        return [
            'message' => $warning->getMessage(),
            'parameters' => $warning->getParameters(),
            'context' => $warning->getContext(),
        ];
    }

    /**
     * @phpstan-param array<string, mixed> $array
     */
    private function warningFromArray(array $array): Warning
    {
        return new Warning($array['message'], $array['parameters'], $array['context']);
    }
}
