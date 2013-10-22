<?php

namespace React\Promise;

class FulfilledPromiseTest extends TestCase
{
    use PromiseTest\PromiseTestTrait,
        PromiseTest\PromiseFulfilledTestTrait;

    public function getPromiseTestAdapter()
    {
        $val = null;
        $promiseCalled = false;

        return [
            'promise' => function () use (&$val, &$promiseCalled) {
                $promiseCalled = true;

                return new FulfilledPromise($val);
            },
            'resolve' => function ($value) use (&$val, &$promiseCalled) {
                if ($promiseCalled) {
                    throw new \LogicException('You must call resolve() before promise() for React\Promise\FulfilledPromise');
                }

                $val = $value;
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
