<?php

declare(strict_types=1);

namespace Yokai\Batch\Registry;

use Psr\Container\ContainerInterface;
use Yokai\Batch\Exception\UndefinedJobException;
use Yokai\Batch\Job\JobInterface;

final class JobRegistry
{
    public function __construct(
        private ContainerInterface $jobs,
    ) {
    }

    /**
     * @throws UndefinedJobException
     */
    public function get(string $name): JobInterface
    {
        if (!$this->jobs->has($name)) {
            throw new UndefinedJobException($name);
        }

        /** @var JobInterface $job */
        $job = $this->jobs->get($name);

        return $job;
    }
}
