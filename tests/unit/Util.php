<?php

namespace Yokai\Batch\Tests\Unit;

final class Util
{
    private function __construct()
    {
    }

    public static function createVarLogger(string $message, string &$var): callable
    {
        return function () use ($message, &$var): void {
            $var .= $message.PHP_EOL;
        };
    }
}
