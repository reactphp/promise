--TEST--
Calling cancel() that rejects afterwards should not report unhandled rejection
--INI--
# suppress legacy PHPUnit 7 warning for Xdebug 3
xdebug.default_enable=
--FILE--
<?php

use React\Promise\Promise;

require __DIR__ . '/../vendor/autoload.php';

/** @var callable(Throwable):void $reject */
$promise = new Promise(function () { }, function ($_, callable $callback) use (&$reject) { $reject = $callback; });
$promise->cancel();

assert($reject instanceof \Closure);
$reject(new \RuntimeException('Cancelled'));

echo 'void' . PHP_EOL;

?>
--EXPECT--
void
