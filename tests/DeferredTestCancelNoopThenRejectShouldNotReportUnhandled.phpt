--TEST--
Calling cancel() and then reject() should not report unhandled rejection
--INI--
# suppress legacy PHPUnit 7 warning for Xdebug 3
xdebug.default_enable=
--FILE--
<?php

use React\Promise\Deferred;

require __DIR__ . '/../vendor/autoload.php';

$deferred = new Deferred();
$deferred->promise()->cancel();
$deferred->reject(new RuntimeException('foo'));

echo 'void' . PHP_EOL;

?>
--EXPECT--
void
