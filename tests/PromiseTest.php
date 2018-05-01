<?php

namespace React\Promise;

use Exception;
use React\Promise\PromiseAdapter\CallbackPromiseAdapter;

class PromiseTest extends TestCase
{
    use PromiseTest\FullTestTrait;

    public function getPromiseTestAdapter(callable $canceller = null)
    {
        $resolveCallback = $rejectCallback = null;

        $promise = new Promise(function ($resolve, $reject) use (&$resolveCallback, &$rejectCallback) {
            $resolveCallback = $resolve;
            $rejectCallback  = $reject;
        }, $canceller);

        return new CallbackPromiseAdapter([
            'promise' => function () use ($promise) {
                return $promise;
            },
            'resolve' => $resolveCallback,
            'reject'  => $rejectCallback,
            'settle'  => $resolveCallback,
        ]);
    }

    /** @test */
    public function shouldRejectIfResolverThrowsException()
    {
        $exception = new Exception('foo');

        $promise = new Promise(function () use ($exception) {
            throw $exception;
        });

        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke')
            ->with(self::identicalTo($exception));

        $promise
            ->then($this->expectCallableNever(), $mock);
    }

    /** @test */
    public function shouldResolveWithoutCreatingGarbageCyclesIfResolverResolvesWithException()
    {
        gc_collect_cycles();
        $promise = new Promise(function ($resolve) {
            $resolve(new \Exception('foo'));
        });
        unset($promise);

        $this->assertSame(0, gc_collect_cycles());
    }

    /** @test */
    public function shouldRejectWithoutCreatingGarbageCyclesIfResolverThrowsExceptionWithoutResolver()
    {
        gc_collect_cycles();
        $promise = new Promise(function () {
            throw new \Exception('foo');
        });
        unset($promise);

        $this->assertSame(0, gc_collect_cycles());
    }

    /** @test */
    public function shouldRejectWithoutCreatingGarbageCyclesIfResolverRejectsWithException()
    {
        gc_collect_cycles();
        $promise = new Promise(function ($resolve, $reject) {
            $reject(new \Exception('foo'));
        });
        unset($promise);

        $this->assertSame(0, gc_collect_cycles());
    }

    /** @test */
    public function shouldRejectWithoutCreatingGarbageCyclesIfCancellerRejectsWithException()
    {
        gc_collect_cycles();
        $promise = new Promise(function ($resolve, $reject) { }, function ($resolve, $reject) {
            $reject(new \Exception('foo'));
        });
        $promise->cancel();
        unset($promise);

        $this->assertSame(0, gc_collect_cycles());
    }

    /** @test */
    public function shouldRejectWithoutCreatingGarbageCyclesIfParentCancellerRejectsWithException()
    {
        gc_collect_cycles();
        $promise = new Promise(function ($resolve, $reject) { }, function ($resolve, $reject) {
            $reject(new \Exception('foo'));
        });
        $promise->then()->then()->then()->cancel();
        unset($promise);

        $this->assertSame(0, gc_collect_cycles());
    }

    /** @test */
    public function shouldRejectWithoutCreatingGarbageCyclesIfResolverThrowsException()
    {
        gc_collect_cycles();
        $promise = new Promise(function ($resolve, $reject) {
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
        gc_collect_cycles();
        $promise = new Promise(function () {
            throw new Exception('foo');
        });
        unset($promise);

        self::assertSame(0, gc_collect_cycles());
    }
}
