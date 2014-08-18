<?php

namespace React\Promise;

use React\Promise\PromiseAdapter\CallbackPromiseAdapter;

class RejectedPromiseTest extends TestCase
{
    use PromiseTest\PromiseTestTrait,
        PromiseTest\PromiseRejectedTestTrait;

    public function getPromiseTestAdapter()
    {
        $promise = null;

        return new CallbackPromiseAdapter([
            'promise' => function () use (&$promise) {
                if (!$promise) {
                    $promise = new RejectedPromise();
                }

                return $promise;
            },
            'resolve' => function () {
                throw new \LogicException('You cannot call resolve() for React\Promise\RejectedPromise');
            },
            'reject' => function ($reason) use (&$promise) {
                if (!$promise) {
                    $promise = new RejectedPromise();
                }

                $promise = new RejectedPromise($reason);
            },
            'progress' => function () {
                throw new \LogicException('You cannot call progress() for React\Promise\RejectedPromise');
            },
        ]);
    }

    /** @test */
    public function shouldThrowExceptionIfConstructedWithAPromise()
    {
        $this->setExpectedException('\InvalidArgumentException');

        return new RejectedPromise(new RejectedPromise());
    }
}
