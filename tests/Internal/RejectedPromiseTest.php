<?php

namespace React\Promise\Internal;

use Exception;
use LogicException;
use React\Promise\PromiseAdapter\CallbackPromiseAdapter;
use React\Promise\PromiseTest\PromiseRejectedTestTrait;
use React\Promise\PromiseTest\PromiseSettledTestTrait;
use React\Promise\TestCase;

class RejectedPromiseTest extends TestCase
{
    use PromiseSettledTestTrait,
        PromiseRejectedTestTrait;

    public function getPromiseTestAdapter(callable $canceller = null)
    {
        $promise = null;

        return new CallbackPromiseAdapter([
            'promise' => function () use (&$promise) {
                if (!$promise) {
                    throw new LogicException('RejectedPromise must be rejected before obtaining the promise');
                }

                return $promise;
            },
            'resolve' => function () {
                throw new LogicException('You cannot call resolve() for React\Promise\RejectedPromise');
            },
            'reject' => function ($reason = null) use (&$promise) {
                if (!$promise) {
                    $promise = new RejectedPromise($reason);
                }
            },
            'settle' => function ($reason = "") use (&$promise) {
                if (!$promise) {
                    if (!$reason instanceof Exception) {
                        $reason = new Exception($reason);
                    }

                    $promise = new RejectedPromise($reason);
                }
            },
        ]);
    }
}
