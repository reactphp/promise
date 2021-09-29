<?php

namespace React\Promise;

use Countable;
use RuntimeException;

class CallbackWithIntersectionTypehintClass
{
    public function __invoke(RuntimeException&Countable $e)
    {
    }

    public function testCallback(RuntimeException&Countable $e)
    {
    }

    public static function testCallbackStatic(RuntimeException&Countable $e)
    {
    }
}
