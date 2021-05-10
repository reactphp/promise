<?php

namespace React\Promise;

use InvalidArgumentException;

class CallbackWithTypehintClass
{
    public function __invoke(InvalidArgumentException $e)
    {
    }

    public function testCallback(InvalidArgumentException $e)
    {
    }

    public static function testCallbackStatic(InvalidArgumentException $e)
    {
    }
}
