--TEST--
Calling resolve() and then then() should not report unhandled rejection
--INI--
# suppress legacy PHPUnit 7 warning for Xdebug 3
xdebug.default_enable=
--FILE--
<?php

use function React\Promise\resolve;

require __DIR__ . '/../vendor/autoload.php';

resolve(42)->then('var_dump');

?>
--EXPECT--
int(42)
