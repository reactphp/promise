--TEST--
The callback given to set_rejection_handler() may trigger a fatal error for unhandled rejection
--INI--
# suppress legacy PHPUnit 7 warning for Xdebug 3
xdebug.default_enable=
--FILE--
<?php

use function React\Promise\reject;
use function React\Promise\set_rejection_handler;

require __DIR__ . '/../vendor/autoload.php';

set_rejection_handler(function (Throwable $e): void {
    trigger_error('Unexpected ' . get_class($e) . ': ' .$e->getMessage(), E_USER_ERROR);
});

reject(new RuntimeException('foo'));

echo 'NEVER';

?>
--EXPECTF--
Fatal error: Unexpected RuntimeException: foo in %s line %d
