--TEST--
The callback given to set_rejection_handler() should not throw an exception or the program should terminate for unhandled rejection
--INI--
# suppress legacy PHPUnit 7 warning for Xdebug 3
xdebug.default_enable=
--FILE--
<?php

use function React\Promise\reject;
use function React\Promise\set_rejection_handler;

require __DIR__ . '/../vendor/autoload.php';

set_rejection_handler(function (Throwable $e): void {
    throw new \UnexpectedValueException('This function should never throw');
});

reject(new RuntimeException('foo'));

echo 'NEVER';

?>
--EXPECTF--
Fatal error: Uncaught UnexpectedValueException from unhandled promise rejection handler: This function should never throw in %s:%d
Stack trace:
#0 %A{main}
