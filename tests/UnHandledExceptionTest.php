<?php

namespace React\Promise;

use Exception;
use React\Promise\Exception\CompositeException;
use React\Promise\Exception\LengthException;

class UnHandledExceptionTest extends TestCase
{
    /** @test */
    public function handleRejectedException()
    {
        $cmd = \PHP_BINARY . ' ' . __DIR__ . DIRECTORY_SEPARATOR . 'child-processes' . DIRECTORY_SEPARATOR . 'handle-rejected-exception.php 2>&1';
        $exitCode = null;
        $output = array();

        exec($cmd, $output, $exitCode);

        self::assertSame(array(), $output);
        self::assertSame(0, $exitCode);
    }
    /** @test */
    public function unhandledRejectedException()
    {
        $cmd = \PHP_BINARY . ' ' . __DIR__ . DIRECTORY_SEPARATOR . 'child-processes' . DIRECTORY_SEPARATOR . 'unhandled-rejected-exception.php 2>&1';
        $exitCode = null;
        $output = array();

        exec($cmd, $output, $exitCode);

        self::assertStringContainsString('PHP Fatal error:  Uncaught Exception: Boom! in', implode(PHP_EOL, $output));
        self::assertSame(255, $exitCode);
    }
}
