<?php

declare(strict_types=1);

namespace Yokai\Batch\Test;

use Exception;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * A very simple container based on an associated array.
 */
final class ArrayContainer implements ContainerInterface
{
    public function __construct(
        /**
         * @var array<string, mixed>
         */
        private array $container,
    ) {
    }

    public function get(string $id): mixed
    {
        if (!isset($this->container[$id])) {
            $message = \sprintf('You have requested a non-existent container entry "%s".', $id);

            throw new class($message) extends Exception implements NotFoundExceptionInterface {
            };
        }

        return $this->container[$id];
    }

    public function has(string $id): bool
    {
        return isset($this->container[$id]);
    }
}
