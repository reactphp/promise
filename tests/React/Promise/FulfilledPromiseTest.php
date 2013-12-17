<?php

namespace React\Promise;

class FulfilledPromiseTest extends TestCase
{
    use PromiseTest\PromiseTestTrait,
        PromiseTest\PromiseFulfilledTestTrait;

    public function getPromiseTestAdapter()
    {
        $promise = null;

        return [
            'promise' => function () use (&$promise) {
                if (!$promise) {
                    $promise = new FulfilledPromise();
                }

                return $promise;
            },
            'resolve' => function ($value) use (&$promise) {
                if (!$promise) {
                    $promise = new FulfilledPromise($value);
                }
            },
            'reject' => function () {
                throw new \LogicException('You cannot call reject() for React\Promise\FulfilledPromise');
            },
            'progress' => function () {
                throw new \LogicException('You cannot call progress() for React\Promise\FulfilledPromise');
            },
        ];
    }

    /** @test */
    public function shouldThrowExceptionIfConstructedWithAPromise()
    {
        $this->setExpectedException('\InvalidArgumentException');

        return new FulfilledPromise(new FulfilledPromise());
    }
}
