<?php

namespace React\Promise;

use Countable;
use RuntimeException;

class CallbackWithIntersectionTypehintClass
{
    #[PHP8] public function __invoke(RuntimeException&Countable $e) { }

    #[PHP8] public function testCallback(RuntimeException&Countable $e) { }

    #[PHP8] public static function testCallbackStatic(RuntimeException&Countable $e) { }
}
