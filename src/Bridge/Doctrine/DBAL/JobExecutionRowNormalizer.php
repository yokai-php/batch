<?php declare(strict_types=1);

namespace Yokai\Batch\Bridge\Doctrine\DBAL;

use DateTimeImmutable;
use DateTimeInterface;
use Yokai\Batch\BatchStatus;
use Yokai\Batch\Failure;
use Yokai\Batch\JobExecution;
use Yokai\Batch\JobParameters;
use Yokai\Batch\Summary;
use Yokai\Batch\Warning;

/**
 * @internal
 */
final class JobExecutionRowNormalizer
{
    /**
     * @var string
     */
    private $idCol = 'id';

    /**
     * @var string
     */
    private $jobNameCol = 'job_name';

    /**
     * @var string
     */
    private $statusCol = 'status';

    /**
     * @var string
     */
    private $parametersCol = 'parameters';

    /**
     * @var string
     */
    private $startTimeCol = 'start_time';

    /**
     * @var string
     */
    private $endTimeCol = 'end_time';

    /**
     * @var string
     */
    private $summaryCol = 'summary';

    /**
     * @var string
     */
    private $failuresCol = 'failures';

    /**
     * @var string
     */
    private $warningsCol = 'warnings';

    /**
     * @var string
     */
    private $childExecutionsCol = 'child_executions';

    /**
     * @var string
     */
    private $dateFormat;

    public function __construct(
        string $idCol,
        string $jobNameCol,
        string $statusCol,
        string $parametersCol,
        string $startTimeCol,
        string $endTimeCol,
        string $summaryCol,
        string $failuresCol,
        string $warningsCol,
        string $childExecutionsCol,
        string $dateFormat
    ) {
        $this->idCol = $idCol;
        $this->jobNameCol = $jobNameCol;
        $this->statusCol = $statusCol;
        $this->parametersCol = $parametersCol;
        $this->startTimeCol = $startTimeCol;
        $this->endTimeCol = $endTimeCol;
        $this->summaryCol = $summaryCol;
        $this->failuresCol = $failuresCol;
        $this->warningsCol = $warningsCol;
        $this->childExecutionsCol = $childExecutionsCol;
        $this->dateFormat = $dateFormat;
    }

    public function toRow(JobExecution $jobExecution): array
    {
        return [
            $this->idCol => $jobExecution->getId(),
            $this->jobNameCol => $jobExecution->getJobName(),
            $this->statusCol => $jobExecution->getStatus()->getValue(),
            $this->parametersCol => iterator_to_array($jobExecution->getParameters()),
            $this->startTimeCol => $jobExecution->getStartTime(),
            $this->endTimeCol => $jobExecution->getEndTime(),
            $this->summaryCol => $jobExecution->getSummary()->all(),
            $this->failuresCol => array_map([$this, 'failureToArray'], $jobExecution->getFailures()),
            $this->warningsCol => array_map([$this, 'warningToArray'], $jobExecution->getWarnings()),
            $this->childExecutionsCol => array_map([$this, 'toChildRow'], $jobExecution->getChildExecutions()),
        ];
    }

    public function fromRow(array $data, JobExecution $parent = null): JobExecution
    {
        $data[$this->statusCol] = intval($data[$this->statusCol]);
        $data[$this->parametersCol] = $this->jsonFromString($data[$this->parametersCol]);
        $data[$this->summaryCol] = $this->jsonFromString($data[$this->summaryCol]);
        $data[$this->failuresCol] = $this->jsonFromString($data[$this->failuresCol]);
        $data[$this->warningsCol] = $this->jsonFromString($data[$this->warningsCol]);
        $data[$this->childExecutionsCol] = $this->jsonFromString($data[$this->childExecutionsCol]);

        $name = $data[$this->jobNameCol];
        $status = new BatchStatus(intval($data[$this->statusCol]));
        $parameters = new JobParameters($data[$this->parametersCol]);
        $summary = new Summary($data[$this->summaryCol]);

        if ($parent !== null) {
            $jobExecution = JobExecution::createChild($parent, $name, $status, $parameters, $summary);
            $parent->addChildExecution($jobExecution);
        } else {
            $jobExecution = JobExecution::createRoot(
                $data[$this->idCol],
                $name,
                $status,
                $parameters,
                $summary
            );
        }

        $jobExecution->setStartTime($this->dateFromString($data[$this->startTimeCol]));
        $jobExecution->setEndTime($this->dateFromString($data[$this->endTimeCol]));

        foreach ($data[$this->failuresCol] as $failureData) {
            $jobExecution->addFailure($this->failureFromArray($failureData));
        }
        foreach ($data[$this->warningsCol] as $warningData) {
            $jobExecution->addWarning($this->warningFromArray($warningData));
        }

        foreach ($data[$this->childExecutionsCol] as $childExecutionData) {
            $jobExecution->addChildExecution($this->fromRow($childExecutionData, $jobExecution));
        }

        return $jobExecution;
    }

    public function toChildRow(JobExecution $jobExecution): array
    {
        return [
            $this->jobNameCol => $jobExecution->getJobName(),
            $this->statusCol => $jobExecution->getStatus()->getValue(),
            $this->parametersCol => iterator_to_array($jobExecution->getParameters()),
            $this->startTimeCol => $this->toDateString($jobExecution->getStartTime()),
            $this->endTimeCol => $this->toDateString($jobExecution->getEndTime()),
            $this->summaryCol => $jobExecution->getSummary()->all(),
            $this->failuresCol => array_map([$this, 'failureToArray'], $jobExecution->getFailures()),
            $this->warningsCol => array_map([$this, 'warningToArray'], $jobExecution->getWarnings()),
            $this->childExecutionsCol => array_map([$this, 'toChildRow'], $jobExecution->getChildExecutions()),
        ];
    }

    private function jsonFromString($value): array
    {
        if (is_string($value)) {
            $value = json_decode($value, true);
        }

        if (!is_array($value)) {
            throw new \LogicException(); //todo
        }

        return $value;
    }

    private function dateFromString(?string $date): ?DateTimeImmutable
    {
        if ($date === null) {
            return null;
        }

        return DateTimeImmutable::createFromFormat($this->dateFormat, $date) ?: null;
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

    private function toDateString(?DateTimeInterface $date): ?string
    {
        if ($date === null) {
            return null;
        }

        return $date->format($this->dateFormat);
    }
}
