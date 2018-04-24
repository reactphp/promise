<?php

namespace React\Promise;

use React\Promise\PromiseAdapter\CallbackPromiseAdapter;

class PromiseTest extends TestCase
{
    use PromiseTest\FullTestTrait;

    public function getPromiseTestAdapter(callable $canceller = null)
    {
        $resolveCallback = $rejectCallback = $progressCallback = null;

        $promise = new Promise(function ($resolve, $reject, $progress) use (&$resolveCallback, &$rejectCallback, &$progressCallback) {
            $resolveCallback  = $resolve;
            $rejectCallback   = $reject;
            $progressCallback = $progress;
        }, $canceller);

        return new CallbackPromiseAdapter([
            'promise' => function () use ($promise) {
                return $promise;
            },
            'resolve' => $resolveCallback,
            'reject'  => $rejectCallback,
            'notify'  => $progressCallback,
            'settle'  => $resolveCallback,
        ]);
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

    /** @test */
    public function shouldRejectWithoutCreatingGarbageCyclesIfResolverThrowsException()
    {
        gc_collect_cycles();
        $promise = new Promise(function () {
            throw new \Exception('foo');
        });
        unset($promise);

        $this->assertSame(0, gc_collect_cycles());
    }

    /**
     * test that checks number of garbage cycles after throwing from a resolver
     * that has its arguments explicitly set to null (reassigned arguments only
     * show up in the stack trace in PHP 7, so we can't test this on legacy PHP)
     *
     * @test
     * @requires PHP 7
     * @link https://3v4l.org/OiDr4
     */
    public function shouldRejectWithoutCreatingGarbageCyclesIfResolverThrowsExceptionWithResolveAndRejectUnset()
    {
        gc_collect_cycles();
        $promise = new Promise(function ($resolve, $reject) {
            $resolve = $reject = null;
            throw new \Exception('foo');
        });
        unset($promise);

        $this->assertSame(0, gc_collect_cycles());
    }

    /** @test */
    public function shouldIgnoreNotifyAfterReject()
    {
        $promise = new Promise(function () { }, function ($resolve, $reject, $notify) {
            $reject(new \Exception('foo'));
            $notify(42);
        });

        $promise->then(null, null, $this->expectCallableNever());
        $promise->cancel();
    }

    /** @test */
    public function shouldFulfillIfFullfilledWithSimplePromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo('foo'));

        $adapter->promise()
            ->then($mock);

        $adapter->resolve(new SimpleFulfilledTestPromise());
    }

    /** @test */
    public function shouldRejectIfRejectedWithSimplePromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo('foo'));

        $adapter->promise()
            ->then($this->expectCallableNever(), $mock);

        $adapter->resolve(new SimpleRejectedTestPromise());
    }
}
