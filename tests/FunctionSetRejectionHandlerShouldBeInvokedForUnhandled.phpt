--TEST--
The callback given to set_rejection_handler() should be invoked for unhandled rejection
--INI--
# suppress legacy PHPUnit 7 warning for Xdebug 3
xdebug.default_enable=
--FILE--
<?php

use function React\Promise\reject;
use function React\Promise\set_rejection_handler;

require __DIR__ . '/../vendor/autoload.php';

set_rejection_handler(function (Throwable $e): void {
    echo 'Unhandled ' . get_class($e) . ': ' . $e->getMessage() . PHP_EOL;
});

reject(new RuntimeException('foo'));

echo 'done' . PHP_EOL;

?>
--EXPECT--
Unhandled RuntimeException: foo
done
