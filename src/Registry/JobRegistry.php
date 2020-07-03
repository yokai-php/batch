<?php

declare(strict_types=1);

namespace Yokai\Batch\Registry;

use Psr\Container\ContainerInterface;
use Yokai\Batch\Exception\UndefinedJobException;
use Yokai\Batch\Job\JobInterface;

final class JobRegistry
{
    /**
     * @var ContainerInterface
     */
    private $jobs;

    /**
     * @param ContainerInterface $jobs
     */
    public function __construct(ContainerInterface $jobs)
    {
        $this->jobs = $jobs;
    }

    /**
     * @param string $name
     *
     * @return JobInterface
     * @throws UndefinedJobException
     */
    public function get(string $name): JobInterface
    {
        if (!$this->jobs->has($name)) {
            throw new UndefinedJobException($name);
        }

        return $this->jobs->get($name);
    }
}
