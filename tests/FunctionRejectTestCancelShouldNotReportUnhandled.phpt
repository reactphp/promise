--TEST--
Calling reject() and then cancel() should not report unhandled rejection
--INI--
# suppress legacy PHPUnit 7 warning for Xdebug 3
xdebug.default_enable=
--FILE--
<?php

use function React\Promise\reject;

require __DIR__ . '/../vendor/autoload.php';

reject(new RuntimeException('foo'))->cancel();

echo 'void' . PHP_EOL;

?>
--EXPECT--
void
