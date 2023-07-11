<?php

use function PHPStan\Testing\assertType;
use function React\Promise\all;
use function React\Promise\resolve;

assertType('React\Promise\PromiseInterface<array<bool>>', all([resolve(true), resolve(false)]));
assertType('React\Promise\PromiseInterface<array<bool>>', all([resolve(true), false]));
assertType('React\Promise\PromiseInterface<array<bool|int>>', all([true, time()]));
assertType('React\Promise\PromiseInterface<array<bool|int>>', all([resolve(true), resolve(time())]));
