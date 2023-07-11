--TEST--
The callback given to the last set_rejection_handler() should be invoked for unhandled rejection
--INI--
# suppress legacy PHPUnit 7 warning for Xdebug 3
xdebug.default_enable=
--FILE--
<?php

use function React\Promise\reject;
use function React\Promise\set_rejection_handler;

require __DIR__ . '/../vendor/autoload.php';

$ret = set_rejection_handler($first = function (Throwable $e): void {
    echo 'THIS WILL NEVER BE CALLED' . PHP_EOL;
});

// previous should be null
var_dump($ret === null);

$ret = set_rejection_handler(function (Throwable $e): void {
    echo 'Unhandled ' . get_class($e) . ': ' . $e->getMessage() . PHP_EOL;
});

// previous rejection handler should be first rejection handler callback
var_dump($ret === $first);

reject(new RuntimeException('foo'));

echo 'done' . PHP_EOL;

?>
--EXPECT--
bool(true)
bool(true)
Unhandled RuntimeException: foo
done
