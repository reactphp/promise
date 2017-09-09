<?php

namespace React\Promise;

class AmpPromiseMock implements \Amp\Promise {
    /** @var PromiseInterface */
    private $promise;

    public function __construct(PromiseInterface $promise) {
        $this->promise = $promise;
    }

    public function onResolve(callable $onResolved) {
        $this->promise->then(function ($value = null) use ($onResolved) {
            $onResolved(null, $value);
        }, function ($exception) use ($onResolved) {
            $onResolved($exception, null);
        });
    }
}

class FunctionAdapt extends TestCase {

    /** @test */
    public function shouldResolve() {
        $value = 1;

        $promise = new AmpPromiseMock(new FulfilledPromise($value));

        $promise = adapt($promise);

        $promise->then(function ($inValue) use (&$result) {
            $result = $inValue;
        });

        $this->assertSame($value, $result);
    }

    /** @test */
    public function shouldReject() {
        $value = 123;

        $promise = new AmpPromiseMock(new RejectedPromise($value));

        $promise = adapt($promise);

        $promise->then(function () {

        }, function ($inValue) use (&$result) {
            $result = $inValue;
        });

        $this->assertSame($value, $result);
    }
}