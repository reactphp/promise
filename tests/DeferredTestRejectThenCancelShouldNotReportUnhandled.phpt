--TEST--
Calling reject() and then cancel() should not report unhandled rejection
--INI--
# suppress legacy PHPUnit 7 warning for Xdebug 3
xdebug.default_enable=
--FILE--
<?php

use React\Promise\Deferred;

require __DIR__ . '/../vendor/autoload.php';

$deferred = new Deferred();
$deferred->reject(new RuntimeException('foo'));
$deferred->promise()->cancel();

echo 'void' . PHP_EOL;

?>
--EXPECT--
void
