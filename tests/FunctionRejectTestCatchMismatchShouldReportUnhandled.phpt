--TEST--
Calling reject() and then catch() with mismatched type should report unhandled rejection
--INI--
# suppress legacy PHPUnit 7 warning for Xdebug 3
xdebug.default_enable=
--FILE--
<?php

use function React\Promise\reject;

require __DIR__ . '/../vendor/autoload.php';

reject(new RuntimeException('foo'))->catch(function (UnexpectedValueException $unexpected): void {
    echo 'This will never be shown because the types do not match' . PHP_EOL;
});

?>
--EXPECTF--
Unhandled promise rejection with RuntimeException: foo in %s:%d
Stack trace:
#0 %A{main}
