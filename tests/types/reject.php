<?php

use React\Promise\PromiseInterface;
use function PHPStan\Testing\assertType;
use function React\Promise\reject;
use function React\Promise\resolve;

assertType('React\Promise\PromiseInterface<never>', reject(new RuntimeException()));
assertType('React\Promise\PromiseInterface<never>', reject(new RuntimeException())->then(null, null));
// assertType('React\Promise\PromiseInterface<never>', reject(new RuntimeException())->then(function (): int {
//     return 42;
// }));
assertType('React\Promise\PromiseInterface<int>', reject(new RuntimeException())->then(null, function (): int {
    return 42;
}));
assertType('React\Promise\PromiseInterface<int>', reject(new RuntimeException())->then(null, function (): PromiseInterface {
    return resolve(42);
}));
// assertType('React\Promise\PromiseInterface<int>', reject(new RuntimeException())->then(function (): bool {
//     return true;
// }, function (): int {
//     return 42;
// }));

assertType('React\Promise\PromiseInterface<int>', reject(new RuntimeException())->catch(function (): int {
    return 42;
}));
assertType('React\Promise\PromiseInterface<int>', reject(new RuntimeException())->catch(function (\UnexpectedValueException $e): int {
    return 42;
}));
assertType('React\Promise\PromiseInterface<int>', reject(new RuntimeException())->catch(function (): PromiseInterface {
    return resolve(42);
}));

assertType('React\Promise\PromiseInterface<never>', reject(new RuntimeException())->finally(function (): void { }));
assertType('React\Promise\PromiseInterface<never>', reject(new RuntimeException())->finally(function (): never {
    throw new \UnexpectedValueException();
}));
assertType('React\Promise\PromiseInterface<never>', reject(new RuntimeException())->finally(function (): PromiseInterface {
    return reject(new \UnexpectedValueException());
}));

assertType('React\Promise\PromiseInterface<int>', reject(new RuntimeException())->otherwise(function (): int {
    return 42;
}));
assertType('React\Promise\PromiseInterface<int>', reject(new RuntimeException())->otherwise(function (\UnexpectedValueException $e): int {
    return 42;
}));
assertType('React\Promise\PromiseInterface<int>', reject(new RuntimeException())->otherwise(function (): PromiseInterface {
    return resolve(42);
}));

assertType('React\Promise\PromiseInterface<never>', reject(new RuntimeException())->always(function (): void { }));
assertType('React\Promise\PromiseInterface<never>', reject(new RuntimeException())->always(function (): never {
    throw new \UnexpectedValueException();
}));
assertType('React\Promise\PromiseInterface<never>', reject(new RuntimeException())->always(function (): PromiseInterface {
    return reject(new \UnexpectedValueException());
}));
