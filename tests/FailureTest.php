<?php

declare(strict_types=1);

namespace Yokai\Batch\Tests;

use Generator;
use LogicException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Yokai\Batch\Failure;

class FailureTest extends TestCase
{
    /**
     * @dataProvider failures
     */
    public function test(
        callable $load,
        string $class,
        int $code,
        string $message,
        array $trace,
        array $parameters,
        string $string
    ): void {
        /** @var Failure $failure */
        $failure = $load();
        self::assertSame($class, $failure->getClass());
        self::assertSame($code, $failure->getCode());
        self::assertSame($message, $failure->getMessage());
        foreach ($trace as $fragment) {
            self::assertStringContainsString($fragment, $failure->getTrace());
        }
        self::assertSame($parameters, $failure->getParameters());
        self::assertSame($string, (string)$failure);
    }

    public function failures(): Generator
    {
        yield [
            fn () => Failure::fromException(
                new LogicException('I will fail because of {var}'),
                ['{var}' => 'test var']
            ),
            'LogicException',
            0,
            'I will fail because of {var}',
            [
                'LogicException: I will fail because of {var}',
            ],
            ['{var}' => 'test var'],
            'I will fail because of test var'
        ];
        yield [
            fn () => Failure::fromException(
                new RuntimeException('This is a test', 123, new LogicException('Previous exception'))
            ),
            'RuntimeException',
            123,
            'This is a test',
            [
                'RuntimeException: This is a test',
                'Caused by: LogicException: Previous exception',
            ],
            [],
            'This is a test'
        ];
    }
}
