<?php

use React\Promise\FulfilledPromise;
use React\Promise\PromiseInterface;
use Throwable;

use function PHPStan\Testing\assertType;
use function React\Promise\all;
use function React\Promise\any;
use function React\Promise\race;
use function React\Promise\reject;
use function React\Promise\resolve;

$passThroughBoolFn = static fn (bool $bool): bool => $bool;
$passThroughThrowable = static function (Throwable $t): PromiseInterface {
    return reject($t);
};
$stringOrInt = function (): int|string {
  return time() % 2 ? 'string' : time();
};
$tosseable = new Exception('Oops I did it again!');

/**
 * basic
 */
assertType('React\Promise\PromiseInterface<bool>', resolve(true));
assertType('React\Promise\PromiseInterface<int|string>', resolve($stringOrInt()));
assertType('React\Promise\PromiseInterface<bool>', resolve(resolve(true)));

/**
 * chaining
 */
assertType('React\Promise\PromiseInterface<bool>', resolve(true)->then($passThroughBoolFn));
assertType('React\Promise\PromiseInterface<bool>', resolve(true)->then()->then($passThroughBoolFn));
assertType('React\Promise\PromiseInterface<bool>', resolve(true)->then(null)->then($passThroughBoolFn));
assertType('React\Promise\PromiseInterface<bool>', resolve(true)->then($passThroughBoolFn)->then($passThroughBoolFn));
assertType('React\Promise\PromiseInterface<bool>', resolve(true)->then($passThroughBoolFn, $passThroughThrowable)->then($passThroughBoolFn));
assertType('React\Promise\PromiseInterface<bool>', resolve(true)->then(null, $passThroughThrowable)->then($passThroughBoolFn));
assertType('React\Promise\PromiseInterface<bool>', resolve(true)->then()->then(null, $passThroughThrowable)->then($passThroughBoolFn));

/**
 * all
 */
assertType('React\Promise\PromiseInterface<array<bool>>', all([resolve(true), resolve(false)]));
assertType('React\Promise\PromiseInterface<array<bool>>', all([resolve(true), false]));
assertType('React\Promise\PromiseInterface<array<bool|int>>', all([true, time()]));
assertType('React\Promise\PromiseInterface<array<bool|int>>', all([resolve(true), resolve(time())]));
assertType('React\Promise\PromiseInterface<array<bool|float>>', all([resolve(true), microtime(true)]));
assertType('React\Promise\PromiseInterface<array<bool|int>>', all([true, resolve(time())]));

/**
 * any
 */
assertType('React\Promise\PromiseInterface<bool>', any([resolve(true), resolve(false)]));
assertType('React\Promise\PromiseInterface<bool>', any([resolve(true), false]));
assertType('React\Promise\PromiseInterface<bool|int>', any([true, time()]));
assertType('React\Promise\PromiseInterface<bool|int>', any([resolve(true), resolve(time())]));
assertType('React\Promise\PromiseInterface<bool|float>', any([resolve(true), microtime(true)]));
assertType('React\Promise\PromiseInterface<bool|int>', any([true, resolve(time())]));

/**
 * race
 */
assertType('React\Promise\PromiseInterface<bool>', race([resolve(true), resolve(false)]));
assertType('React\Promise\PromiseInterface<bool>', race([resolve(true), false]));
assertType('React\Promise\PromiseInterface<bool|int>', race([true, time()]));
assertType('React\Promise\PromiseInterface<bool|int>', race([resolve(true), resolve(time())]));
assertType('React\Promise\PromiseInterface<bool|float>', race([resolve(true), microtime(true)]));
assertType('React\Promise\PromiseInterface<bool|int>', race([true, resolve(time())]));

/**
 * direct class access (deprecated!!!)
 */
assertType('React\Promise\FulfilledPromise<bool>', new FulfilledPromise(true));
assertType('React\Promise\PromiseInterface<bool>', (new FulfilledPromise(true))->then($passThroughBoolFn));
