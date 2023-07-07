--TEST--
Calling cancel() that rejects should not report unhandled rejection
--INI--
# suppress legacy PHPUnit 7 warning for Xdebug 3
xdebug.default_enable=
--FILE--
<?php

use React\Promise\Promise;

require __DIR__ . '/../vendor/autoload.php';

$promise = new Promise(function () { }, function () { throw new \RuntimeException('Cancelled'); });
$promise->cancel();

echo 'void' . PHP_EOL;

?>
--EXPECT--
void
