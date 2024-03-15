<?php

namespace React\Promise\Internal;

use InvalidArgumentException;
use LogicException;
use React\Promise\PromiseAdapter\CallbackPromiseAdapter;
use React\Promise\PromiseTest\PromiseFulfilledTestTrait;
use React\Promise\PromiseTest\PromiseSettledTestTrait;
use React\Promise\TestCase;

/**
 * @template T
 */
class FulfilledPromiseTest extends TestCase
{
    use PromiseSettledTestTrait,
        PromiseFulfilledTestTrait;

    /**
     * @return CallbackPromiseAdapter<T>
     */
    public function getPromiseTestAdapter(?callable $canceller = null): CallbackPromiseAdapter
    {
        /** @var ?FulfilledPromise<T> */
        $promise = null;

        return new CallbackPromiseAdapter([
            'promise' => function () use (&$promise) {
                if (!$promise) {
                    throw new LogicException('FulfilledPromise must be resolved before obtaining the promise');
                }

                return $promise;
            },
            'resolve' => function ($value = null) use (&$promise) {
                if (!$promise) {
                    $promise = new FulfilledPromise($value);
                }
            },
            'reject' => function () {
                throw new LogicException('You cannot call reject() for React\Promise\FulfilledPromise');
            },
            'settle' => function ($value = null) use (&$promise) {
                if (!$promise) {
                    $promise = new FulfilledPromise($value);
                }
            },
        ]);
    }

    /**
     * @test
     */
    public function shouldThrowExceptionIfConstructedWithAPromise(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new FulfilledPromise(new FulfilledPromise());
    }
}
