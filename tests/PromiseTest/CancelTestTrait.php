<?php

namespace React\Promise\PromiseTest;

use React\Promise;

trait CancelTestTrait
{
    /**
     * @return \React\Promise\PromiseAdapter\PromiseAdapterInterface
     */
    abstract public function getPromiseTestAdapter(callable $canceller = null);

    /** @test */
    public function cancelShouldCallCancellerWithResolverArguments()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isType('callable'), $this->isType('callable'));

        $adapter = $this->getPromiseTestAdapter($mock);

        $adapter->promise()->cancel();
    }

    /** @test */
    public function cancelShouldFulfillPromiseIfCancellerFulfills()
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
    public function cancelShouldRejectPromiseIfCancellerRejects()
    {
        $exception = new \Exception();

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
    public function cancelShouldRejectPromiseWithExceptionIfCancellerThrows()
    {
        $e = new \Exception();

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
    public function cancelShouldCallCancellerOnlyOnceIfCancellerResolves()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->will($this->returnCallback(function ($resolve) {
                $resolve();
            }));

        $adapter = $this->getPromiseTestAdapter($mock);

        $adapter->promise()->cancel();
        $adapter->promise()->cancel();
    }

    /** @test */
    public function cancelShouldHaveNoEffectIfCancellerDoesNothing()
    {
        $adapter = $this->getPromiseTestAdapter(function () {});

        $adapter->promise()
            ->then($this->expectCallableNever(), $this->expectCallableNever());

        $adapter->promise()->cancel();
        $adapter->promise()->cancel();
    }

    /** @test */
    public function cancelShouldCallCancellerFromDeepNestedPromiseChain()
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
    public function cancelCalledOnChildrenSouldOnlyCancelWhenAllChildrenCancelled()
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
    public function cancelShouldTriggerCancellerWhenAllChildrenCancel()
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
    public function cancelShouldNotTriggerCancellerWhenCancellingOneChildrenMultipleTimes()
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
    public function cancelShouldTriggerCancellerOnlyOnceWhenCancellingMultipleTimes()
    {
        $adapter = $this->getPromiseTestAdapter($this->expectCallableOnce());

        $adapter->promise()->cancel();
        $adapter->promise()->cancel();
    }

    /** @test */
    public function cancelShouldAlwaysTriggerCancellerWhenCalledOnRootPromise()
    {
        $adapter = $this->getPromiseTestAdapter($this->expectCallableOnce());

        $adapter->promise()
            ->then()
            ->then();

        $adapter->promise()
            ->then();

        $adapter->promise()->cancel();
    }
}
