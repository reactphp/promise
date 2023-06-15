<?php

namespace React\Promise;

use Countable;
use RuntimeException;

class CallbackWithDNFTypehintClass
{
    #[PHP8] public function __invoke((RuntimeException&Countable)|(RuntimeException&\IteratorAggregate) $e) { }

    #[PHP8] public function testCallback((RuntimeException&Countable)|(RuntimeException&\IteratorAggregate) $e) { }

    #[PHP8] public static function testCallbackStatic((RuntimeException&Countable)|(RuntimeException&\IteratorAggregate) $e) { }
}
