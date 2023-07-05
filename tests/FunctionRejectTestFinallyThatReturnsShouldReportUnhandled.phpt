--TEST--
Calling reject() and then finally() should call handler and report unhandled rejection
--INI--
# suppress legacy PHPUnit 7 warning for Xdebug 3
xdebug.default_enable=
--FILE--
<?php

use function React\Promise\reject;

require __DIR__ . '/../vendor/autoload.php';

reject(new RuntimeException('foo'))->finally(function (): void {
    echo 'Foo' . PHP_EOL;
});

?>
--EXPECTF--
Foo
Unhandled promise rejection with RuntimeException: foo in %s:%d
Stack trace:
#0 %A{main}
