<?php

namespace Yokai\Batch\Tests\Unit\Bridge\Symfony\Console;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Process\PhpExecutableFinder;
use Yokai\Batch\Bridge\Symfony\Console\CommandRunner;

class CommandRunnerTest extends TestCase
{
    /**
     * @return MockObject|CommandRunner
     */
    private function createRunner(): MockObject
    {
        /** @var PhpExecutableFinder|ObjectProphecy $phpLocator */
        $phpLocator = $this->prophesize(PhpExecutableFinder::class);
        $phpLocator->find()->willReturn('/usr/bin/php');

        return $this->getMockBuilder(CommandRunner::class)
            ->setMethods(['exec'])
            ->setConstructorArgs(['/path/to/bin', '/path/to/logs', $phpLocator->reveal()])
            ->getMock();
    }

    public function testRun(): void
    {
        $runner = $this->createRunner();
        $runner->expects($this->once())
            ->method('exec')
            ->with('/usr/bin/php /path/to/bin/console yokai:testing:test 1 \'{"json":["value",2]}"\' --opt --option=foo');

        $runner->run(
            'yokai:testing:test',
            ['arg1' => '1', 'arg2' => '{"json":["value",2]}"', '--opt', '--option' => 'foo']
        );
    }

    public function testRunAsync(): void
    {
        $runner = $this->createRunner();
        $runner->expects($this->once())
            ->method('exec')
            ->with('/usr/bin/php /path/to/bin/console yokai:testing:test 1 \'{"json":["value",2]}"\' --opt --option=foo >> /path/to/logs/test.log 2>&1 &');

        $runner->runAsync(
            'yokai:testing:test',
            'test.log',
            ['arg1' => '1', 'arg2' => '{"json":["value",2]}"', '--opt', '--option' => 'foo']
        );
    }
}
