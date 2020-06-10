<?php declare(strict_types=1);

namespace Yokai\Batch;

use Throwable;

final class Failure
{
    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $message;

    /**
     * @var int
     */
    private $code;

    /**
     * @var string
     */
    private $file;

    /**
     * @var int
     */
    private $line;

    /**
     * @var array
     */
    private $parameters;

    /**
     * @var string|null
     */
    private $trace;

    /**
     * @param string      $class
     * @param string      $message
     * @param int         $code
     * @param string      $file
     * @param int         $line
     * @param array       $parameters
     * @param string|null $trace
     */
    public function __construct(
        string $class,
        string $message,
        int $code,
        string $file,
        int $line,
        array $parameters = [],
        string $trace = null
    ) {
        $this->class = $class;
        $this->message = $message;
        $this->code = $code;
        $this->file = $file;
        $this->line = $line;
        $this->parameters = $parameters;
        $this->trace = $trace;
    }

    /**
     * @param Throwable $exception
     * @param array     $parameters
     *
     * @return Failure
     */
    public static function fromException(Throwable $exception, array $parameters = []): self
    {
        return new self(
            get_class($exception),
            $exception->getMessage(),
            $exception->getCode(),
            $exception->getFile(),
            $exception->getLine(),
            $parameters,
            $exception->getTraceAsString()
        );
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return strtr($this->message, $this->parameters);
    }

    /**
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getFile(): string
    {
        return $this->file;
    }

    /**
     * @return int
     */
    public function getLine(): int
    {
        return $this->line;
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @return string|null
     */
    public function getTrace(): ?string
    {
        return $this->trace;
    }
}
