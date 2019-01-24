<?php

namespace Yokai\Batch\Tests\Unit\Bridge\Symfony\Console;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Yokai\Batch\BatchStatus;
use Yokai\Batch\Bridge\Symfony\Console\RunJobCommand;
use Yokai\Batch\Failure;
use Yokai\Batch\JobExecution;
use Yokai\Batch\Launcher\JobLauncherInterface;
use Yokai\Batch\Warning;

class RunJobCommandTest extends TestCase
{
    private const JOBNAME = 'testing';

    /**
     * @var JobLauncherInterface|ObjectProphecy
     */
    private $jobLauncher;

    protected function setUp()
    {
        $this->jobLauncher = $this->prophesize(JobLauncherInterface::class);
    }

    protected function tearDown()
    {
        unset($this->jobLauncher);
    }

    private function execute(string $configuration = null, int $verbosity = OutputInterface::VERBOSITY_NORMAL): array
    {
        $options = ['verbosity' => $verbosity];
        $input = ['job' => self::JOBNAME];
        if ($configuration !== null) {
            $input['configuration'] = $configuration;
        }

        $tester = new CommandTester(new RunJobCommand($this->jobLauncher->reveal()));
        $tester->execute($input, $options);

        return [$tester->getStatusCode(), $tester->getDisplay()];
    }

    /**
     * @expectedException \Symfony\Component\Console\Exception\InvalidArgumentException
     * @dataProvider invalidJson
     */
    public function testRunWithMalformedConfiguration(string $invalidJson): void
    {
        $this->jobLauncher->launch(Argument::cetera())->shouldNotBeCalled();
        $this->execute($invalidJson);
    }

    /**
     * @dataProvider verbosity
     */
    public function testRunWithErrors(int $verbosity): void
    {
        $jobExecution = JobExecution::createRoot('123456789', self::JOBNAME, new BatchStatus(BatchStatus::FAILED));
        $jobExecution->addFailure(Failure::fromException($runtime = new \RuntimeException('1st exception')));
        $jobExecution->addFailure(Failure::fromException($logic = new \LogicException('2nd exception')));
        $this->jobLauncher->launch(self::JOBNAME, [])
            ->shouldBeCalledTimes(1)
            ->willReturn($jobExecution);

        list($code, $display) = $this->execute(null, $verbosity);

        self::assertSame(RunJobCommand::EXIT_ERROR_CODE, $code);

        if ($verbosity === OutputInterface::VERBOSITY_QUIET) {
            self::assertSame('', $display);
        } else {
            self::assertContains('An error occurred during the testing execution.', $display);
            self::assertContains('Error #0 of class RuntimeException: 1st exception', $display);
            self::assertContains('Error #0 of class LogicException: 2nd exception', $display);
            if ($verbosity > OutputInterface::VERBOSITY_NORMAL) {
                self::assertContains($runtime->getTraceAsString(), $display);
                self::assertContains($logic->getTraceAsString(), $display);
            } else {
                self::assertNotContains($runtime->getTraceAsString(), $display);
                self::assertNotContains($logic->getTraceAsString(), $display);
            }
        }
    }

    /**
     * @dataProvider verbosity
     */
    public function testRunWithWarnings(int $verbosity): void
    {
        $jobExecution = JobExecution::createRoot('123456789', self::JOBNAME, new BatchStatus(BatchStatus::COMPLETED));
        $jobExecution->addWarning(new Warning('1st warning'));
        $jobExecution->addWarning(new Warning('2nd warning'));
        $this->jobLauncher->launch(self::JOBNAME, [])
            ->shouldBeCalledTimes(1)
            ->willReturn($jobExecution);

        list($code, $display) = $this->execute(null, $verbosity);

        self::assertSame(RunJobCommand::EXIT_WARNING_CODE, $code);

        if ($verbosity === OutputInterface::VERBOSITY_QUIET) {
            self::assertSame('', $display);
        } else {
            self::assertContains('testing has been executed with 2 warnings.', $display);
            if ($verbosity > OutputInterface::VERBOSITY_NORMAL) {
                self::assertContains('1st warning', $display);
                self::assertContains('2nd warning', $display);
            } else {
                self::assertNotContains('1st warning', $display);
                self::assertNotContains('2nd warning', $display);
            }
        }
    }

    /**
     * @dataProvider verbosity
     */
    public function testRunSuccessful(int $verbosity): void
    {
        $jobExecution = JobExecution::createRoot('123456789', self::JOBNAME, new BatchStatus(BatchStatus::COMPLETED));
        $this->jobLauncher->launch(self::JOBNAME, [])
            ->shouldBeCalledTimes(1)
            ->willReturn($jobExecution);

        list($code, $display) = $this->execute(null, $verbosity);

        self::assertSame(RunJobCommand::EXIT_SUCCESS_CODE, $code);
    }

    public function invalidJson(): \Generator
    {
        yield ['{]'];
    }

    public function verbosity(): \Generator
    {
        yield [OutputInterface::VERBOSITY_QUIET];
        yield [OutputInterface::VERBOSITY_NORMAL];
        yield [OutputInterface::VERBOSITY_VERBOSE];
        yield [OutputInterface::VERBOSITY_VERY_VERBOSE];
        yield [OutputInterface::VERBOSITY_DEBUG];
    }
}
