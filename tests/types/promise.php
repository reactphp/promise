<?php

use React\Promise\Promise;
use function PHPStan\Testing\assertType;

// $promise = new Promise(function (): void { });
// assertType('React\Promise\PromiseInterface<never>', $promise);

// $promise = new Promise(function (callable $resolve): void {
//     $resolve(42);
// });
// assertType('React\Promise\PromiseInterface<int>', $promise);

// $promise = new Promise(function (callable $resolve): void {
//     $resolve(true);
//     $resolve('ignored');
// });
// assertType('React\Promise\PromiseInterface<bool>', $promise);

// $promise = new Promise(function (callable $resolve, callable $reject): void {
//     $reject(new \RuntimeException());
// });
// assertType('React\Promise\PromiseInterface<never>', $promise);

// $promise = new Promise(function (): never {
//     throw new \RuntimeException();
// });
// assertType('React\Promise\PromiseInterface<never>', $promise);

// invalid number of arguments for $resolver
/** @phpstan-ignore-next-line */
$promise = new Promise(function ($a, $b, $c) { });
assert($promise instanceof Promise);
// assertType('React\Promise\PromiseInterface<never>', $promise);

// invalid types for arguments of $resolver
/** @phpstan-ignore-next-line */
$promise = new Promise(function (int $a, string $b) { });
// assertType('React\Promise\PromiseInterface<never>', $promise);

// invalid number of arguments passed to $resolve
$promise = new Promise(function (callable $resolve) {
    /** @phpstan-ignore-next-line */
    $resolve();
});
// assertType('React\Promise\PromiseInterface<never>', $promise);

// invalid number of arguments passed to $reject
$promise = new Promise(function (callable $resolve, callable $reject) {
    /** @phpstan-ignore-next-line */
    $reject();
});
// assertType('React\Promise\PromiseInterface<never>', $promise);

// invalid type passed to $reject
$promise = new Promise(function (callable $resolve, callable $reject) {
    /** @phpstan-ignore-next-line */
    $reject(2);
});
// assertType('React\Promise\PromiseInterface<never>', $promise);

// invalid number of arguments for $canceller
/** @phpstan-ignore-next-line */
$promise = new Promise(function () { }, function ($a, $b, $c) { });
// assertType('React\Promise\PromiseInterface<never>', $promise);

// invalid types for arguments of $canceller
/** @phpstan-ignore-next-line */
$promise = new Promise(function () { }, function (int $a, string $b) { });
// assertType('React\Promise\PromiseInterface<never>', $promise);

// invalid number of arguments passed to $resolve
$promise = new Promise(function () { }, function (callable $resolve) {
    /** @phpstan-ignore-next-line */
    $resolve();
});
// assertType('React\Promise\PromiseInterface<never>', $promise);

// invalid number of arguments passed to $reject
$promise = new Promise(function () { }, function (callable $resolve, callable $reject) {
    /** @phpstan-ignore-next-line */
    $reject();
});
// assertType('React\Promise\PromiseInterface<never>', $promise);

// invalid type passed to $reject
$promise = new Promise(function() { }, function (callable $resolve, callable $reject) {
    /** @phpstan-ignore-next-line */
    $reject(2);
});
// assertType('React\Promise\PromiseInterface<never>', $promise);
