<?php

namespace Yokai\Batch\Bridge\Symfony\Messenger;

final class LaunchJobMessage
{
    /**
     * @var string
     */
    private $jobName;

    /**
     * @var array
     */
    private $configuration;

    public function __construct(string $jobName, array $configuration = [])
    {
        $this->jobName = $jobName;
        $this->configuration = $configuration;
    }

    public function getJobName(): string
    {
        return $this->jobName;
    }

    public function getConfiguration(): array
    {
        return $this->configuration;
    }
}
