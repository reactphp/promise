<?php

use function PHPStan\Testing\assertType;
use function React\Promise\any;
use function React\Promise\resolve;

assertType('React\Promise\PromiseInterface<bool>', any([resolve(true), resolve(false)]));
assertType('React\Promise\PromiseInterface<bool>', any([resolve(true), false]));
assertType('React\Promise\PromiseInterface<bool|int>', any([true, time()]));
assertType('React\Promise\PromiseInterface<bool|int>', any([resolve(true), resolve(time())]));
