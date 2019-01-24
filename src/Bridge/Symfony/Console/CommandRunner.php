<?php declare(strict_types=1);

namespace Yokai\Batch\Bridge\Symfony\Console;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Process\PhpExecutableFinder;

class CommandRunner
{
    /**
     * @var string
     */
    private $consolePath;

    /**
     * @var string
     */
    private $logDir;

    /**
     * @var PhpExecutableFinder
     */
    private $phpLocator;

    public function __construct(string $binDir, string $logDir, PhpExecutableFinder $phpLocator = null)
    {
        $this->consolePath = implode(DIRECTORY_SEPARATOR, [$binDir, 'console']);
        $this->logDir = $logDir;
        $this->phpLocator = $phpLocator ?: new PhpExecutableFinder();
    }

    /**
     * @param string $commandName
     * @param array  $arguments
     */
    public function run(string $commandName, array $arguments = []): void
    {
        $this->exec($this->buildCommand($commandName, $arguments));
    }

    /**
     * @param string $commandName
     * @param string $logFilename
     * @param array  $arguments
     */
    public function runAsync(string $commandName, string $logFilename, array $arguments = []): void
    {
        $this->exec(
            sprintf(
                '%s >> %s 2>&1 &',
                $this->buildCommand($commandName, $arguments),
                implode(DIRECTORY_SEPARATOR, [$this->logDir, $logFilename])
            )
        );
    }

    protected function exec(string $command): void
    {
        exec($command);
    }

    private function buildCommand(string $commandName, array $arguments): string
    {
        return sprintf(
            '%s %s %s %s',
            $this->phpLocator->find(),
            $this->consolePath,
            $commandName,
            (string)(new ArrayInput($arguments))
        );
    }
}
