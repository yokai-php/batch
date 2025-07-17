<?php

declare(strict_types=1);

namespace Yokai\Batch\Launcher;

use Psr\Container\ContainerInterface;
use Yokai\Batch\Exception\UnexpectedValueException;
use Yokai\Batch\JobExecution;

/**
 * This {@see JobLauncherInterface} is a proxy locator to other {@see JobLauncherInterface}.
 * It will allow you to configure the relation between job and launcher.
 * It requires a collection of {@see JobLauncherInterface} along with a default one, and some configuration.
 *
 * Whenever it is asked to launch a job, if that job is configured with a routing entry,
 * the corresponding {@see JobLauncherInterface} will be used.
 * Otherwise, this is the provided default {@see JobLauncherInterface} that will be used.
 */
final class RoutingJobLauncher implements JobLauncherInterface
{
    public function __construct(
        private ContainerInterface $launchers,
        private JobLauncherInterface $default,
        /**
         * @var array<string, string>
         */
        private array $routing,
    ) {
    }

    public function launch(string $name, array $configuration = []): JobExecution
    {
        $launcherName = $this->routing[$name] ?? null;
        if ($launcherName === null) {
            $launcher = $this->default;
        } else {
            $launcher = $this->launchers->get($launcherName);
            if (!$launcher instanceof JobLauncherInterface) {
                throw UnexpectedValueException::type(JobLauncherInterface::class, $launcher);
            }
        }

        return $launcher->launch($name, $configuration);
    }
}
