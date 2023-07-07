--TEST--
Calling reject() and then then() should report unhandled rejection for new exception from handler
--INI--
# suppress legacy PHPUnit 7 warning for Xdebug 3
xdebug.default_enable=
--FILE--
<?php

use function React\Promise\reject;

require __DIR__ . '/../vendor/autoload.php';

reject(new RuntimeException('foo'))->then(null, function () {
    throw new \RuntimeException('bar');
});

?>
--EXPECTF--
Unhandled promise rejection with RuntimeException: bar in %s:%d
Stack trace:
#0 %s/src/Internal/RejectedPromise.php(%d): {closure}(%S)
#1 %s(%d): React\Promise\Internal\RejectedPromise->then(%S)
#2 %A{main}
