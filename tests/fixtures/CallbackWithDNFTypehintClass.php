<?php

namespace React\Promise;

use Countable;
use RuntimeException;

class CallbackWithDNFTypehintClass
{
    public function __invoke((RuntimeException&Countable)|(RuntimeException&\ArrayAccess) $e)
    {
    }

public function testCallback((RuntimeException&Countable)|(RuntimeException&\ArrayAccess) $e)
    {
    }

    public static function testCallbackStatic((RuntimeException&Countable)|(RuntimeException&\ArrayAccess) $e)
    {
    }
}
