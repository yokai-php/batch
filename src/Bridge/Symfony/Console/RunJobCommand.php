<?php

declare(strict_types=1);

namespace Yokai\Batch\Bridge\Symfony\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Yokai\Batch\BatchStatus;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Launcher\JobLauncherInterface;

final class RunJobCommand extends Command
{
    public const EXIT_SUCCESS_CODE = 0;
    public const EXIT_ERROR_CODE = 1;
    public const EXIT_WARNING_CODE = 2;

    /**
     * @var JobLauncherInterface
     */
    private $jobLauncher;

    /**
     * @param JobLauncherInterface $jobLauncher
     */
    public function __construct(JobLauncherInterface $jobLauncher)
    {
        parent::__construct(null);
        $this->jobLauncher = $jobLauncher;
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setName('yokai:batch:run')
            ->addArgument('job', InputArgument::REQUIRED)
            ->addArgument('configuration', InputArgument::OPTIONAL);
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $jobName = $input->getArgument('job');
        $configuration = $this->decodeConfiguration($input->getArgument('configuration') ?? '[]');

        $execution = $this->jobLauncher->launch($jobName, $configuration);

        $this->outputExecution($execution, $output);

        return $this->guessExecutionExitCode($execution);
    }

    private function guessExecutionExitCode(JobExecution $jobExecution): int
    {
        if ($jobExecution->getStatus()->is(BatchStatus::COMPLETED)) {
            if (count($jobExecution->getAllWarnings()) === 0) {
                return self::EXIT_SUCCESS_CODE;
            }

            return self::EXIT_WARNING_CODE;
        }

        return self::EXIT_ERROR_CODE;
    }

    private function outputExecution(JobExecution $jobExecution, OutputInterface $output): void
    {
        $jobName = $jobExecution->getJobName();
        if ($jobExecution->getStatus()->is(BatchStatus::COMPLETED)) {
            $warnings = $jobExecution->getAllWarnings();
            if (count($warnings)) {
                foreach ($warnings as $warning) {
                    $output->writeln(sprintf('<comment>%s</comment>', $warning), $output::VERBOSITY_VERBOSE);
                }
                $output->writeln(
                    sprintf('<comment>%s has been executed with %d warnings.</comment>', $jobName, count($warnings))
                );
            } else {
                $output->writeln(
                    sprintf('<info>%s has been successfully executed.</info>', $jobName)
                );
            }
        } else {
            $output->writeln(
                sprintf('<error>An error occurred during the %s execution.</error>', $jobName)
            );
            foreach ($jobExecution->getAllFailures() as $failure) {
                $output->writeln(
                    sprintf(
                        '<error>Error #%s of class %s: %s</error>',
                        $failure->getCode(),
                        $failure->getClass(),
                        $failure
                    )
                );
                if ($failure->getTrace() !== null) {
                    $output->writeln(sprintf('<error>%s</error>', $failure->getTrace()), $output::VERBOSITY_VERBOSE);
                }
            }
        }
    }

    /**
     * @param string $data
     *
     * @throws InvalidArgumentException
     * @return array
     */
    private function decodeConfiguration($data): array
    {
        $config = json_decode($data, true);

        $error = 'Cannot decode JSON';

        switch (json_last_error()) {
            case JSON_ERROR_DEPTH:
                $error = 'Maximum stack depth exceeded';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $error = 'Underflow or the modes mismatch';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $error = 'Unexpected control character found';
                break;
            case JSON_ERROR_SYNTAX:
                $error = 'Syntax error, malformed JSON';
                break;
            case JSON_ERROR_UTF8:
                $error = 'Malformed UTF-8 characters, possibly incorrectly encoded';
                break;
            case JSON_ERROR_NONE:
                return $config;
        }

        throw new InvalidArgumentException($error);
    }
}
