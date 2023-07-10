--TEST--
The callback given to set_rejection_handler() should be invoked for outer unhandled rejection and may set new rejection handler for inner unhandled rejection
--INI--
# suppress legacy PHPUnit 7 warning for Xdebug 3
xdebug.default_enable=
--FILE--
<?php

use function React\Promise\reject;
use function React\Promise\set_rejection_handler;

require __DIR__ . '/../vendor/autoload.php';

set_rejection_handler(function (Throwable $e): void {
    $ret = set_rejection_handler(function (Throwable $e): void {
        echo 'Unhandled inner ' . get_class($e) . ': ' . $e->getMessage() . PHP_EOL;
    });

    // previous rejection handler should be unset while handling a rejection
    var_dump($ret === null);

    reject(new \UnexpectedValueException('bar'));

    echo 'Unhandled outer ' . get_class($e) . ': ' . $e->getMessage() . PHP_EOL;
});

reject(new RuntimeException('foo'));

echo 'done' . PHP_EOL;

?>
--EXPECT--
bool(true)
Unhandled inner UnexpectedValueException: bar
Unhandled outer RuntimeException: foo
done
