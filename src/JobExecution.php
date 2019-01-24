<?php declare(strict_types=1);

namespace Yokai\Batch;

use DateInterval;
use DateTime;
use DateTimeInterface;
use Throwable;
use Yokai\Batch\Exception\ImmutablePropertyException;

final class JobExecution
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $jobName;

    /**
     * @var BatchStatus
     */
    private $status;

    /**
     * @var DateTimeInterface|null
     */
    private $startTime;

    /**
     * @var DateTimeInterface|null
     */
    private $endTime;

    /**
     * @var Failure[]
     */
    private $failures = [];

    /**
     * @var Warning[]
     */
    private $warnings = [];

    /**
     * @var Summary
     */
    private $summary;

    /**
     * @var JobParameters
     */
    private $parameters;

    /**
     * @var null|JobExecution
     */
    private $parentExecution;

    /**
     * @var JobExecution[]
     */
    private $childExecutions = [];

    /**
     * @param null|JobExecution  $parentExecution
     * @param string             $id
     * @param string             $jobName
     * @param BatchStatus|null   $status
     * @param JobParameters|null $parameters
     * @param Summary|null       $summary
     */
    private function __construct(
        ?JobExecution $parentExecution,
        string $id,
        string $jobName,
        BatchStatus $status = null,
        JobParameters $parameters = null,
        Summary $summary = null
    ) {
        $this->parentExecution = $parentExecution;
        $this->id = $id;
        $this->jobName = $jobName;
        $this->status = $status ?: new BatchStatus(BatchStatus::PENDING);
        $this->parameters = $parameters ?: new JobParameters();
        $this->summary = $summary ?: new Summary();
    }

    public static function createRoot(
        string $id,
        string $jobName,
        BatchStatus $status = null,
        JobParameters $parameters = null,
        Summary $summary = null
    ): self {
        return new self(null, $id, $jobName, $status, $parameters, $summary);
    }

    public static function createChild(
        JobExecution $parent,
        string $jobName,
        BatchStatus $status = null,
        JobParameters $parameters = null,
        Summary $summary = null
    ): self {
        return new self($parent, $parent->getId(), $jobName, $status, $parameters, $summary);
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getJobName(): string
    {
        return $this->jobName;
    }

    /**
     * @return BatchStatus
     */
    public function getStatus(): BatchStatus
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus(int $status): void
    {
        if ($status > $this->status->getValue()) {
            $this->status = new BatchStatus($status);
        }
    }

    /**
     * @return DateTimeInterface
     */
    public function getStartTime(): ?DateTimeInterface
    {
        return $this->startTime;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getEndTime(): ?DateTimeInterface
    {
        return $this->endTime;
    }

    /**
     * @return DateInterval
     */
    public function getDuration(): DateInterval
    {
        $now = new DateTime();
        $start = $this->startTime ?: $now;
        $end = $this->endTime ?: $now;

        return $start->diff($end);
    }

    /**
     * @param DateTimeInterface|null $startTime
     *
     * @throws ImmutablePropertyException
     */
    public function setStartTime(?DateTimeInterface $startTime): void
    {
        if ($this->startTime !== null) {
            throw new ImmutablePropertyException(__CLASS__, 'startTime');
        }

        $this->startTime = $startTime;
    }

    /**
     * @param DateTimeInterface|null $endTime
     *
     * @throws ImmutablePropertyException
     */
    public function setEndTime(?DateTimeInterface $endTime): void
    {
        if ($this->endTime !== null) {
            throw new ImmutablePropertyException(__CLASS__, 'endTime');
        }

        $this->endTime = $endTime;
    }

    /**
     * @return Summary
     */
    public function getSummary(): Summary
    {
        return $this->summary;
    }

    /**
     * @param string $childName
     *
     * @return JobExecution
     */
    public function createChildExecution(string $childName): JobExecution
    {
        return self::createChild($this, $childName);
    }

    /**
     * @return null|JobExecution
     */
    public function getParentExecution(): ?JobExecution
    {
        return $this->parentExecution;
    }

    /**
     * @return JobExecution
     */
    public function getRootExecution(): JobExecution
    {
        $execution = $this;
        while (null !== $parent = $execution->getParentExecution()) {
            $execution = $parent;
        }

        return $execution;
    }

    /**
     * @return JobExecution[]
     */
    public function getChildExecutions(): array
    {
        return array_values($this->childExecutions);
    }

    /**
     * @param JobExecution $execution
     */
    public function addChildExecution(JobExecution $execution): void
    {
        $this->childExecutions[$execution->getJobName()] = $execution;
    }

    /**
     * @param string $childName
     *
     * @return JobExecution
     */
    public function getChildExecution(string $childName): ?JobExecution
    {
        return $this->childExecutions[$childName] ?? null;
    }

    /**
     * @return JobParameters
     */
    public function getParameters(): JobParameters
    {
        return $this->parameters;
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getParameter(string $name)
    {
        return $this->parameters->get($name);
    }

    /**
     * @return Failure[]
     */
    public function getFailures(): array
    {
        return $this->failures;
    }

    /**
     * @param Failure $failure
     */
    public function addFailure(Failure $failure): void
    {
        $this->failures[] = $failure;
    }

    /**
     * @param Throwable $exception
     * @param array     $parameters
     */
    public function addFailureException(Throwable $exception, array $parameters = []): void
    {
        $this->addFailure(Failure::fromException($exception, $parameters));
    }

    /**
     * Get self failures, and merge those with children failures
     *
     * @return Failure[]
     */
    public function getAllFailures(): array
    {
        $self = $this->getFailures();
        $children = [];
        foreach ($this->getChildExecutions() as $childExecution) {
            $children = array_merge($children, $childExecution->getFailures());
        }

        return array_merge($self, $children);
    }

    /**
     * @return Warning[]
     */
    public function getWarnings(): array
    {
        return $this->warnings;
    }

    /**
     * @param Warning $warning
     */
    public function addWarning(Warning $warning): void
    {
        $this->warnings[] = $warning;
    }

    /**
     * Get self warnings, and merge those with children warnings
     *
     * @return Warning[]
     */
    public function getAllWarnings(): array
    {
        $self = $this->getWarnings();
        $children = [];
        foreach ($this->getChildExecutions() as $childExecution) {
            $children = array_merge($children, $childExecution->getWarnings());
        }

        return array_merge($self, $children);
    }
}
