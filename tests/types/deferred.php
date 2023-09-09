<?php

use React\Promise\Deferred;
use function PHPStan\Testing\assertType;

$deferredA = new Deferred();
assertType('React\Promise\PromiseInterface<mixed>', $deferredA->promise());

/** @var Deferred<int> $deferredB */
$deferredB = new Deferred();
$deferredB->resolve(42);
assertType('React\Promise\PromiseInterface<int>', $deferredB->promise());

// $deferred = new Deferred();
// $deferred->resolve(42);
// assertType('React\Promise\Deferred<int>', $deferred);

// $deferred = new Deferred();
// $deferred->resolve(true);
// $deferred->resolve('ignored');
// assertType('React\Promise\Deferred<bool>', $deferred);

// $deferred = new Deferred();
// $deferred->reject(new \RuntimeException());
// assertType('React\Promise\Deferred<never>', $deferred);

// invalid number of arguments passed to $canceller
/** @phpstan-ignore-next-line */
$deferred = new Deferred(function ($a, $b, $c) { });
assertType('React\Promise\Deferred<mixed>', $deferred);

// invalid types for arguments of $canceller
/** @phpstan-ignore-next-line */
$deferred = new Deferred(function (int $a, string $b) { });
assertType('React\Promise\Deferred<mixed>', $deferred);

// invalid number of arguments passed to $resolve
$deferred = new Deferred(function (callable $resolve) {
    /** @phpstan-ignore-next-line */
    $resolve();
});
assertType('React\Promise\Deferred<mixed>', $deferred);

// invalid type passed to $reject
$deferred = new Deferred(function (callable $resolve, callable $reject) {
    /** @phpstan-ignore-next-line */
    $reject(2);
});
assertType('React\Promise\Deferred<mixed>', $deferred);
