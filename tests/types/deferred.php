<?php

use React\Promise\Deferred;
use function PHPStan\Testing\assertType;

$deferredA = new Deferred();
assertType('React\Promise\PromiseInterface<mixed>', $deferredA->promise());

/** @var Deferred<int> $deferredB */
$deferredB = new Deferred();
$deferredB->resolve(42);
assertType('React\Promise\PromiseInterface<int>', $deferredB->promise());
