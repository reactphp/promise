<?php

namespace React\Promise;

class RejectedPromiseTest extends TestCase
{
    use PromiseTest\PromiseTestTrait,
        PromiseTest\PromiseRejectedTestTrait;

    public function getPromiseTestAdapter()
    {
        $val = null;
        $promiseCalled = false;

        return [
            'promise' => function () use (&$val, &$promiseCalled) {
                $promiseCalled = true;

                return new RejectedPromise($val);
            },
            'resolve' => function ($value) {
                throw new \LogicException('You cannot call resolve() for React\Promise\RejectedPromise');
            },
            'reject' => function ($reason) use (&$val, &$promiseCalled) {
                if ($promiseCalled) {
                    throw new \LogicException('You must call reject() before promise() for React\Promise\RejectedPromise');
                }

                $val = $reason;
            },
            'progress' => function () {
                throw new \LogicException('You cannot call progress() for React\Promise\RejectedPromise');
            },
        ];
    }

    /** @test */
    public function shouldThrowExceptionIfConstructedWithAPromise()
    {
        $this->setExpectedException('\InvalidArgumentException');

        return new RejectedPromise(new RejectedPromise());
    }
}
