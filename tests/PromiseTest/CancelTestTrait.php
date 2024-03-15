<?php

namespace React\Promise\PromiseTest;

use Exception;
use React\Promise;
use React\Promise\PromiseAdapter\PromiseAdapterInterface;

trait CancelTestTrait
{
    abstract public function getPromiseTestAdapter(?callable $canceller = null): PromiseAdapterInterface;

    /** @test */
    public function cancelShouldCallCancellerWithResolverArguments(): void
    {
        $args = [];
        $adapter = $this->getPromiseTestAdapter(function ($resolve, $reject) use (&$args) {
            $args = func_get_args();
        });

        $adapter->promise()->cancel();

        self::assertCount(2, $args);
        self::assertTrue(is_callable($args[0]));
        self::assertTrue(is_callable($args[1]));
    }

    /** @test */
    public function cancelShouldCallCancellerWithoutArgumentsIfNotAccessed(): void
    {
        $args = null;
        $adapter = $this->getPromiseTestAdapter(function () use (&$args) {
            $args = func_num_args();
        });

        $adapter->promise()->cancel();

        self::assertSame(0, $args);
    }

    /** @test */
    public function cancelShouldFulfillPromiseIfCancellerFulfills(): void
    {
        $adapter = $this->getPromiseTestAdapter(function ($resolve) {
            $resolve(1);
        });

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $adapter->promise()
            ->then($mock, $this->expectCallableNever());

        $adapter->promise()->cancel();
    }

    /** @test */
    public function cancelShouldRejectPromiseIfCancellerRejects(): void
    {
        $exception = new Exception();

        $adapter = $this->getPromiseTestAdapter(function ($resolve, $reject) use ($exception) {
            $reject($exception);
        });

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->promise()
            ->then($this->expectCallableNever(), $mock);

        $adapter->promise()->cancel();
    }

    /** @test */
    public function cancelShouldRejectPromiseWithExceptionIfCancellerThrows(): void
    {
        $e = new Exception();

        $adapter = $this->getPromiseTestAdapter(function () use ($e) {
            throw $e;
        });

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($e));

        $adapter->promise()
            ->then($this->expectCallableNever(), $mock);

        $adapter->promise()->cancel();
    }

    /** @test */
    public function cancelShouldCallCancellerOnlyOnceIfCancellerResolves(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->will($this->returnCallback(function ($resolve) {
                $resolve(null);
            }));

        $adapter = $this->getPromiseTestAdapter($mock);

        $adapter->promise()->cancel();
        $adapter->promise()->cancel();
    }

    /** @test */
    public function cancelShouldHaveNoEffectIfCancellerDoesNothing(): void
    {
        $adapter = $this->getPromiseTestAdapter(function () {});

        $adapter->promise()
            ->then($this->expectCallableNever(), $this->expectCallableNever());

        $adapter->promise()->cancel();
        $adapter->promise()->cancel();
    }

    /** @test */
    public function cancelShouldCallCancellerFromDeepNestedPromiseChain(): void
    {
        $adapter = $this->getPromiseTestAdapter($this->expectCallableOnce());

        $promise = $adapter->promise()
            ->then(function () {
                return new Promise\Promise(function () {});
            })
            ->then(function () {
                $d = new Promise\Deferred();

                return $d->promise();
            })
            ->then(function () {
                return new Promise\Promise(function () {});
            });

        $promise->cancel();
    }

    /** @test */
    public function cancelCalledOnChildrenSouldOnlyCancelWhenAllChildrenCancelled(): void
    {
        $adapter = $this->getPromiseTestAdapter($this->expectCallableNever());

        $child1 = $adapter->promise()
            ->then()
            ->then();

        $adapter->promise()
            ->then();

        $child1->cancel();
    }

    /** @test */
    public function cancelShouldTriggerCancellerWhenAllChildrenCancel(): void
    {
        $adapter = $this->getPromiseTestAdapter($this->expectCallableOnce());

        $child1 = $adapter->promise()
            ->then()
            ->then();

        $child2 = $adapter->promise()
            ->then();

        $child1->cancel();
        $child2->cancel();
    }

    /** @test */
    public function cancelShouldNotTriggerCancellerWhenCancellingOneChildrenMultipleTimes(): void
    {
        $adapter = $this->getPromiseTestAdapter($this->expectCallableNever());

        $child1 = $adapter->promise()
            ->then()
            ->then();

        $child2 = $adapter->promise()
            ->then();

        $child1->cancel();
        $child1->cancel();
    }

    /** @test */
    public function cancelShouldTriggerCancellerOnlyOnceWhenCancellingMultipleTimes(): void
    {
        $adapter = $this->getPromiseTestAdapter($this->expectCallableOnce());

        $adapter->promise()->cancel();
        $adapter->promise()->cancel();
    }

    /** @test */
    public function cancelShouldAlwaysTriggerCancellerWhenCalledOnRootPromise(): void
    {
        $adapter = $this->getPromiseTestAdapter($this->expectCallableOnce());

        $adapter->promise()
            ->then()
            ->then();

        $adapter->promise()
            ->then();

        $adapter->promise()->cancel();
    }

    /** @test */
    public function cancelShouldTriggerCancellerWhenFollowerCancels(): void
    {
        $adapter1 = $this->getPromiseTestAdapter($this->expectCallableOnce());

        $root = $adapter1->promise();

        $adapter2 = $this->getPromiseTestAdapter($this->expectCallableOnce());

        $follower = $adapter2->promise();
        $adapter2->resolve($root);

        $follower->cancel();
    }

    /** @test */
    public function cancelShouldNotTriggerCancellerWhenCancellingOnlyOneFollower(): void
    {
        $adapter1 = $this->getPromiseTestAdapter($this->expectCallableNever());

        $root = $adapter1->promise();

        $adapter2 = $this->getPromiseTestAdapter($this->expectCallableOnce());

        $follower1 = $adapter2->promise();
        $adapter2->resolve($root);

        $adapter3 = $this->getPromiseTestAdapter($this->expectCallableNever());
        $adapter3->resolve($root);

        $follower1->cancel();
    }

    /** @test */
    public function cancelCalledOnFollowerShouldOnlyCancelWhenAllChildrenAndFollowerCancelled(): void
    {
        $adapter1 = $this->getPromiseTestAdapter($this->expectCallableOnce());

        $root = $adapter1->promise();

        $child = $root->then();

        $adapter2 = $this->getPromiseTestAdapter($this->expectCallableOnce());

        $follower = $adapter2->promise();
        $adapter2->resolve($root);

        $follower->cancel();
        $child->cancel();
    }

    /** @test */
    public function cancelShouldNotTriggerCancellerWhenCancellingFollowerButNotChildren(): void
    {
        $adapter1 = $this->getPromiseTestAdapter($this->expectCallableNever());

        $root = $adapter1->promise();

        $root->then();

        $adapter2 = $this->getPromiseTestAdapter($this->expectCallableOnce());

        $follower = $adapter2->promise();
        $adapter2->resolve($root);

        $follower->cancel();
    }
}
