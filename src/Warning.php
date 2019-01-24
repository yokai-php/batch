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
     * @param string $message
     * @param array  $parameters
     */
    public function __construct(string $message, array $parameters = [])
    {
        $this->message = $message;
        $this->parameters = $parameters;
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
}
