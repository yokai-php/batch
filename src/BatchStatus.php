<?php declare(strict_types=1);

namespace Yokai\Batch;

final class BatchStatus
{
    public const PENDING = 1;
    public const RUNNING = 2;
    public const STOPPED = 3;
    public const COMPLETED = 4;
    public const ABANDONED = 5;
    public const FAILED = 6;

    private const LABELS = [
        self::PENDING => 'PENDING',
        self::RUNNING => 'RUNNING',
        self::STOPPED => 'STOPPED',
        self::COMPLETED => 'COMPLETED',
        self::ABANDONED => 'ABANDONED',
        self::FAILED => 'FAILED',
    ];

    /**
     * @var int
     */
    private $value;

    /**
     * @param int $value
     */
    public function __construct(int $value)
    {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return self::LABELS[$this->value] ?? 'UNKNOWN';
    }

    /**
     * @return int
     */
    public function getValue(): int
    {
        return $this->value;
    }

    /**
     * @param int $value
     *
     * @return bool
     */
    public function is(int $value): bool
    {
        return $this->value === $value;
    }

    /**
     * @param array $values
     *
     * @return bool
     */
    public function isOneOf(array $values): bool
    {
        return in_array($this->value, $values, true);
    }

    /**
     * @return bool
     */
    public function isUnsuccessful(): bool
    {
        return $this->isOneOf([self::ABANDONED, self::STOPPED, self::FAILED]);
    }

    /**
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return $this->isOneOf([self::COMPLETED]);
    }

    /**
     * @return bool
     */
    public function isExecutable(): bool
    {
        return $this->is(self::PENDING);
    }
}
