<?php

use React\Promise\PromiseInterface;
use function PHPStan\Testing\assertType;
use function React\Promise\reject;
use function React\Promise\resolve;

/**
 * @return int|string
 */
function stringOrInt() {
    return time() % 2 ? 'string' : time();
};

/**
 * @return PromiseInterface<int|string>
 */
function stringOrIntPromise(): PromiseInterface {
    return resolve(time() % 2 ? 'string' : time());
};

assertType('React\Promise\PromiseInterface<bool>', resolve(true));
assertType('React\Promise\PromiseInterface<int|string>', resolve(stringOrInt()));
assertType('React\Promise\PromiseInterface<int|string>', stringOrIntPromise());
assertType('React\Promise\PromiseInterface<bool>', resolve(resolve(true)));

assertType('React\Promise\PromiseInterface<bool>', resolve(true)->then(null, null));
assertType('React\Promise\PromiseInterface<bool>', resolve(true)->then(function (bool $bool): bool {
    return $bool;
}));
assertType('React\Promise\PromiseInterface<int>', resolve(true)->then(function (bool $value): int {
    return 42;
}));
assertType('React\Promise\PromiseInterface<int>', resolve(true)->then(function (bool $value): PromiseInterface {
    return resolve(42);
}));
assertType('React\Promise\PromiseInterface<never>', resolve(true)->then(function (bool $value): never {
    throw new \RuntimeException();
}));
assertType('React\Promise\PromiseInterface<bool|int>', resolve(true)->then(null, function (\Throwable $e): int {
    return 42;
}));

assertType('React\Promise\PromiseInterface<void>', resolve(true)->then(function (bool $bool): void { }));
assertType('React\Promise\PromiseInterface<void>', resolve(false)->then(function (bool $bool): void { })->then(function (null $value) { }));

$value = null;
assertType('React\Promise\PromiseInterface<void>', resolve(true)->then(static function (bool $v) use (&$value): void {
    $value = $v;
}));
assertType('bool|null', $value);

assertType('React\Promise\PromiseInterface<bool>', resolve(true)->catch(function (\Throwable $e): never {
    throw $e;
}));
assertType('React\Promise\PromiseInterface<bool|int>', resolve(true)->catch(function (\Throwable $e): int {
    return 42;
}));
assertType('React\Promise\PromiseInterface<bool|int>', resolve(true)->catch(function (\Throwable $e): PromiseInterface {
    return resolve(42);
}));

assertType('React\Promise\PromiseInterface<bool>', resolve(true)->finally(function (): void { }));
// assertType('React\Promise\PromiseInterface<never>', resolve(true)->finally(function (): never {
//     throw new \RuntimeException();
// }));
// assertType('React\Promise\PromiseInterface<never>', resolve(true)->finally(function (): PromiseInterface {
//     return reject(new \RuntimeException());
// }));

assertType('React\Promise\PromiseInterface<bool>', resolve(true)->otherwise(function (\Throwable $e): never {
    throw $e;
}));
assertType('React\Promise\PromiseInterface<bool|int>', resolve(true)->otherwise(function (\Throwable $e): int {
    return 42;
}));
assertType('React\Promise\PromiseInterface<bool|int>', resolve(true)->otherwise(function (\Throwable $e): PromiseInterface {
    return resolve(42);
}));

assertType('React\Promise\PromiseInterface<bool>', resolve(true)->always(function (): void { }));
// assertType('React\Promise\PromiseInterface<never>', resolve(true)->always(function (): never {
//     throw new \RuntimeException();
// }));
// assertType('React\Promise\PromiseInterface<never>', resolve(true)->always(function (): PromiseInterface {
//     return reject(new \RuntimeException());
// }));
