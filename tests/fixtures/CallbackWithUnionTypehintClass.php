<?php

namespace React\Promise;

use InvalidArgumentException;
use RuntimeException;

class CallbackWithUnionTypehintClass
{
    #[PHP8] public function __invoke(RuntimeException|InvalidArgumentException $e) { }

    #[PHP8] public function testCallback(RuntimeException|InvalidArgumentException $e) { }

    #[PHP8] public static function testCallbackStatic(RuntimeException|InvalidArgumentException $e) { }
}
