<?php declare(strict_types=1);

namespace Yokai\Batch;

final class Warning
{
    /**
     * @var string
     */
    private $message;

    /**
     * @var array
     */
    private $parameters;

    /**
     * @var array
     */
    private $context;

    public function __construct(string $message, array $parameters = [], array $context = [])
    {
        $this->message = $message;
        $this->parameters = $parameters;
        $this->context = $context;
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
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
    /**
     * @return array
     */
    public function getContext(): array
    {
        return $this->context;
    }
}
