<?php

namespace React\Promise;

use InvalidArgumentException;
use RuntimeException;

class CallbackWithUnionTypehintClass
{
    public function __invoke(RuntimeException|InvalidArgumentException $e)
    {
    }

    public function testCallback(RuntimeException|InvalidArgumentException $e)
    {
    }

    public static function testCallbackStatic(RuntimeException|InvalidArgumentException $e)
    {
    }
}
