--TEST--
Calling any() with rejected promises should report unhandled rejection
--INI--
# suppress legacy PHPUnit 7 warning for Xdebug 3
xdebug.default_enable=
--FILE--
<?php

use function React\Promise\any;
use function React\Promise\reject;

require __DIR__ . '/../vendor/autoload.php';

any([
    reject(new RuntimeException('foo')),
    reject(new RuntimeException('bar'))
]);

?>
--EXPECTF--
Unhandled promise rejection with React\Promise\Exception\CompositeException: All promises rejected. in %s:%d
Stack trace:
#0 %s/src/Promise.php(%d): React\Promise\{closure}(%S)
#1 %s/src/Promise.php(%d): React\Promise\Promise->call(%S)
#2 %s/src/functions.php(%d): React\Promise\Promise->__construct(%S)
#3 %s(%d): React\Promise\any(%S)
#4 %A{main}

