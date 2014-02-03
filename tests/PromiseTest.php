<?php

namespace React\Promise;

class PromiseTest extends TestCase
{
    use PromiseTest\FullTestTrait;

    public function getPromiseTestAdapter()
    {
        $resolveCallback = $rejectCallback = $progressCallback = null;

        $promise = new Promise(function ($resolve, $reject, $progress) use (&$resolveCallback, &$rejectCallback, &$progressCallback) {
            $resolveCallback  = $resolve;
            $rejectCallback   = $reject;
            $progressCallback = $progress;
        });

        return [
            'promise' => function () use ($promise) {
                return $promise;
            },
            'resolve'  => $resolveCallback,
            'reject'   => $rejectCallback,
            'progress' => $progressCallback,
        ];
    }

    /** @test */
    public function shouldRejectIfResolverThrowsException()
    {
        $exception = new \Exception('foo');

        $promise = new Promise(function () use ($exception) {
            throw $exception;
        });

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $promise
            ->then($this->expectCallableNever(), $mock);
    }
}
