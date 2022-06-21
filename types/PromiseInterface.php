<?php

use function PHPStan\Testing\assertType;
use function React\Promise\resolve;

$passThroughBoolFn = static fn (bool $bool): bool => $bool;

assertType('React\Promise\PromiseInterface<bool>', resolve(true));
assertType('React\Promise\PromiseInterface<bool>', resolve(true)->then($passThroughBoolFn));
