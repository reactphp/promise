--TEST--
Calling reject() and then finally() should call handler and report unhandled rejection for new exception from handler
--INI--
# suppress legacy PHPUnit 7 warning for Xdebug 3
xdebug.default_enable=
--FILE--
<?php

use function React\Promise\reject;

require __DIR__ . '/../vendor/autoload.php';

reject(new RuntimeException('foo'))->finally(function (): void {
    throw new \RuntimeException('Finally!');
});

?>
--EXPECTF--
Unhandled promise rejection with RuntimeException: Finally! in %s:%d
Stack trace:
#0 %s/src/Internal/RejectedPromise.php(%d): {closure}(%S)
#1 %s/src/Internal/RejectedPromise.php(%d): React\Promise\Internal\RejectedPromise->React\Promise\Internal\{closure}(%S)
#2 %s/src/Internal/RejectedPromise.php(%d): React\Promise\Internal\RejectedPromise->then(%S)
#3 %s(%d): React\Promise\Internal\RejectedPromise->finally(%S)
#4 %A{main}
