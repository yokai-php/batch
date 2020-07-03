<?php

declare(strict_types=1);

namespace Yokai\Batch\Bridge\Symfony\Serializer;

use DateTimeImmutable;
use DateTimeInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Yokai\Batch\BatchStatus;
use Yokai\Batch\Failure;
use Yokai\Batch\JobExecution;
use Yokai\Batch\JobExecutionLogs;
use Yokai\Batch\JobParameters;
use Yokai\Batch\Summary;
use Yokai\Batch\Warning;

final class JobExecutionNormalizer implements
    NormalizerInterface,
    DenormalizerInterface
{
    /**
     * @inheritdoc
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof JobExecution;
    }

    /**
     * @inheritdoc
     */
    public function normalize($object, $format = null, array $context = []): array
    {
        return $this->toArray($object);
    }

    /**
     * @inheritdoc
     */
    public function supportsDenormalization($data, $type, $format = null): bool
    {
        return $type === JobExecution::class;
    }

    /**
     * @inheritdoc
     */
    public function denormalize($data, $class, $format = null, array $context = []): JobExecution
    {
        return $this->fromArray($data);
    }

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
            $jobExecution->addFailure($this->failureFromArray($failureData));
        }
        foreach ($jobExecutionData['warnings'] as $warningData) {
            $jobExecution->addWarning($this->warningFromArray($warningData));
        }

        foreach ($jobExecutionData['childExecutions'] as $childExecutionData) {
            $jobExecution->addChildExecution($this->fromArray($childExecutionData, $jobExecution));
        }

        return $jobExecution;
    }

    private function dateToString(?DateTimeInterface $date): ?string
    {
        if ($date === null) {
            return $date;
        }

        return $date->format(DateTimeInterface::ISO8601);
    }

    private function stringToDate(?string $date): ?DateTimeInterface
    {
        if ($date === null) {
            return $date;
        }

        $dateObject = DateTimeImmutable::createFromFormat(DateTimeInterface::ISO8601, $date);
        if ($dateObject === false) {
            throw new \RuntimeException(
                sprintf('Expecting a ISO8601 (%s) date. Got "%s"', DateTimeInterface::ISO8601, $date)
            );
        }

        return $dateObject;
    }

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

    private function warningToArray(Warning $warning): array
    {
        return [
            'message' => $warning->getMessage(),
            'parameters' => $warning->getParameters(),
            'context' => $warning->getContext(),
        ];
    }

    private function warningFromArray(array $array): Warning
    {
        return new Warning($array['message'], $array['parameters'], $array['context']);
    }
}
