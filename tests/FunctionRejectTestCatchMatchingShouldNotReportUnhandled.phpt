--TEST--
Calling reject() and then catch() with matching type should not report unhandled rejection
--INI--
# suppress legacy PHPUnit 7 warning for Xdebug 3
xdebug.default_enable=
--FILE--
<?php

use function React\Promise\reject;

require __DIR__ . '/../vendor/autoload.php';

reject(new RuntimeException('foo'))->catch(function (RuntimeException $e): void {
    echo 'Handled ' . get_class($e) . ': ' . $e->getMessage() . PHP_EOL;
});

?>
--EXPECT--
Handled RuntimeException: foo
