<?php

declare(strict_types=1);

namespace Yokai\Batch\Registry;

use Exception;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Yokai\Batch\Job\JobInterface;

/**
 * This {@see ContainerInterface} implementation
 * suits for providing a static implementation to {@see JobRegistry}.
 *
 * You can instead use any psr/container implementation
 * {@see https://packagist.org/providers/psr/container-implementation}
 */
final class JobContainer implements ContainerInterface
{
    public function __construct(
        /**
         * @var array<string, JobInterface>
         */
        private array $jobs,
    ) {
    }

    public function get(string $id): JobInterface
    {
        if (!isset($this->jobs[$id])) {
            $message = \sprintf('You have requested a non-existent job "%s".', $id);
            throw new class ($message) extends Exception implements NotFoundExceptionInterface {
            };
        }

        return $this->jobs[$id];
    }

    public function has(string $id): bool
    {
        return isset($this->jobs[$id]);
    }
}
