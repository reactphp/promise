--TEST--
Calling race() with rejected promises should report unhandled rejection
--INI--
# suppress legacy PHPUnit 7 warning for Xdebug 3
xdebug.default_enable=
--FILE--
<?php

use function React\Promise\race;
use function React\Promise\reject;

require __DIR__ . '/../vendor/autoload.php';

race([
    reject(new RuntimeException('foo')),
    reject(new RuntimeException('bar'))
]);

?>
--EXPECTF--
Unhandled promise rejection with RuntimeException: foo in %s:%d
Stack trace:
#0 %A{main}
