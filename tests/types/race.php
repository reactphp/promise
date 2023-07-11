<?php

use function PHPStan\Testing\assertType;
use function React\Promise\race;
use function React\Promise\resolve;

assertType('React\Promise\PromiseInterface<bool>', race([resolve(true), resolve(false)]));
assertType('React\Promise\PromiseInterface<bool>', race([resolve(true), false]));
assertType('React\Promise\PromiseInterface<bool|int>', race([true, time()]));
assertType('React\Promise\PromiseInterface<bool|int>', race([resolve(true), resolve(time())]));
