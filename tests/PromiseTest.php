<?php

namespace React\Promise;

use Exception;
use React\Promise\PromiseAdapter\CallbackPromiseAdapter;

/**
 * @template T
 */
class PromiseTest extends TestCase
{
    use PromiseTest\FullTestTrait;

    /**
     * @return CallbackPromiseAdapter<T>
     */
    public function getPromiseTestAdapter(?callable $canceller = null): CallbackPromiseAdapter
    {
        $resolveCallback = $rejectCallback = null;

        $promise = new Promise(function (callable $resolve, callable $reject) use (&$resolveCallback, &$rejectCallback): void {
            $resolveCallback = $resolve;
            $rejectCallback  = $reject;
        }, $canceller);

        assert(is_callable($resolveCallback));
        assert(is_callable($rejectCallback));

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
    public function shouldRejectIfResolverThrowsException(): void
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
    public function shouldResolveWithoutCreatingGarbageCyclesIfResolverResolvesWithException(): void
    {
        gc_collect_cycles();
        $promise = new Promise(function ($resolve) {
            $resolve(new \Exception('foo'));
        });
        unset($promise);

        $this->assertSame(0, gc_collect_cycles());
    }

    /** @test */
    public function shouldRejectWithoutCreatingGarbageCyclesIfResolverThrowsExceptionWithoutResolver(): void
    {
        gc_collect_cycles();
        $promise = new Promise(function () {
            throw new \Exception('foo');
        });

        $promise->then(null, $this->expectCallableOnce()); // avoid reporting unhandled rejection

        unset($promise);

        $this->assertSame(0, gc_collect_cycles());
    }

    /** @test */
    public function shouldRejectWithoutCreatingGarbageCyclesIfResolverRejectsWithException(): void
    {
        gc_collect_cycles();
        $promise = new Promise(function ($resolve, $reject) {
            $reject(new \Exception('foo'));
        });

        $promise->then(null, $this->expectCallableOnce()); // avoid reporting unhandled rejection

        unset($promise);

        $this->assertSame(0, gc_collect_cycles());
    }

    /** @test */
    public function shouldRejectWithoutCreatingGarbageCyclesIfCancellerRejectsWithException(): void
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
    public function shouldRejectWithoutCreatingGarbageCyclesIfParentCancellerRejectsWithException(): void
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
    public function shouldRejectWithoutCreatingGarbageCyclesIfResolverThrowsException(): void
    {
        gc_collect_cycles();
        $promise = new Promise(function ($resolve, $reject) {
            throw new \Exception('foo');
        });

        $promise->then(null, $this->expectCallableOnce()); // avoid reporting unhandled rejection

        unset($promise);

        $this->assertSame(0, gc_collect_cycles());
    }

    /**
     * Test that checks number of garbage cycles after throwing from a canceller
     * that explicitly uses a reference to the promise. This is rather synthetic,
     * actual use cases often have implicit (hidden) references which ought not
     * to be stored in the stack trace.
     *
     * Reassigned arguments only show up in the stack trace in PHP 7, so we can't
     * avoid this on legacy PHP. As an alternative, consider explicitly unsetting
     * any references before throwing.
     *
     * @test
     * @requires PHP 7
     */
    public function shouldRejectWithoutCreatingGarbageCyclesIfCancellerWithReferenceThrowsException(): void
    {
        gc_collect_cycles();
        /** @var Promise<never> $promise */
        $promise = new Promise(function () {}, function () use (&$promise) {
            assert($promise instanceof Promise);
            throw new \Exception('foo');
        });
        $promise->cancel();
        unset($promise);

        $this->assertSame(0, gc_collect_cycles());
    }

    /**
     * @test
     * @requires PHP 7
     * @see self::shouldRejectWithoutCreatingGarbageCyclesIfCancellerWithReferenceThrowsException
     */
    public function shouldRejectWithoutCreatingGarbageCyclesIfResolverWithReferenceThrowsException(): void
    {
        gc_collect_cycles();
        /** @var Promise<never> $promise */
        $promise = new Promise(function () use (&$promise) {
            assert($promise instanceof Promise);
            throw new \Exception('foo');
        });

        $promise->then(null, $this->expectCallableOnce()); // avoid reporting unhandled rejection

        unset($promise);

        $this->assertSame(0, gc_collect_cycles());
    }

    /**
     * @test
     * @requires PHP 7
     * @see self::shouldRejectWithoutCreatingGarbageCyclesIfCancellerWithReferenceThrowsException
     */
    public function shouldRejectWithoutCreatingGarbageCyclesIfCancellerHoldsReferenceAndResolverThrowsException(): void
    {
        gc_collect_cycles();
        /** @var Promise<never> $promise */
        $promise = new Promise(function () {
            throw new \Exception('foo');
        }, function () use (&$promise) {
            assert($promise instanceof Promise);
        });

        $promise->then(null, $this->expectCallableOnce()); // avoid reporting unhandled rejection

        unset($promise);

        $this->assertSame(0, gc_collect_cycles());
    }

    /** @test */
    public function shouldNotLeaveGarbageCyclesWhenRemovingLastReferenceToPendingPromise(): void
    {
        gc_collect_cycles();
        $promise = new Promise(function () { });
        unset($promise);

        $this->assertSame(0, gc_collect_cycles());
    }

    /** @test */
    public function shouldNotLeaveGarbageCyclesWhenRemovingLastReferenceToPendingPromiseWithThenFollowers(): void
    {
        gc_collect_cycles();
        $promise = new Promise(function () { });
        $promise->then()->then()->then();
        unset($promise);

        $this->assertSame(0, gc_collect_cycles());
    }

    /** @test */
    public function shouldNotLeaveGarbageCyclesWhenRemovingLastReferenceToPendingPromiseWithCatchFollowers(): void
    {
        gc_collect_cycles();
        $promise = new Promise(function () { });
        $promise->catch(function () { });
        unset($promise);

        $this->assertSame(0, gc_collect_cycles());
    }

    /** @test */
    public function shouldNotLeaveGarbageCyclesWhenRemovingLastReferenceToPendingPromiseWithFinallyFollowers(): void
    {
        gc_collect_cycles();
        $promise = new Promise(function () { });
        $promise->finally(function () { });
        unset($promise);

        $this->assertSame(0, gc_collect_cycles());
    }

    /**
     * @test
     * @deprecated
     */
    public function shouldNotLeaveGarbageCyclesWhenRemovingLastReferenceToPendingPromiseWithOtherwiseFollowers(): void
    {
        gc_collect_cycles();
        $promise = new Promise(function () { });
        $promise->otherwise(function () { });
        unset($promise);

        $this->assertSame(0, gc_collect_cycles());
    }

    /**
     * @test
     * @deprecated
     */
    public function shouldNotLeaveGarbageCyclesWhenRemovingLastReferenceToPendingPromiseWithAlwaysFollowers(): void
    {
        gc_collect_cycles();
        $promise = new Promise(function () { });
        $promise->always(function () { });
        unset($promise);

        $this->assertSame(0, gc_collect_cycles());
    }

    /** @test */
    public function shouldFulfillIfFullfilledWithSimplePromise(): void
    {
        gc_collect_cycles();
        $promise = new Promise(function () {
            throw new Exception('foo');
        });

        $promise->then(null, $this->expectCallableOnce()); // avoid reporting unhandled rejection

        unset($promise);

        self::assertSame(0, gc_collect_cycles());
    }
}
