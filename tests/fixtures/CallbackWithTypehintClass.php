<?php

namespace React\Promise;

use InvalidArgumentException;

class CallbackWithTypehintClass
{
    public function __invoke(InvalidArgumentException $e): void
    {
    }

    public function testCallback(InvalidArgumentException $e): void
    {
    }

    public static function testCallbackStatic(InvalidArgumentException $e): void
    {
    }
}
