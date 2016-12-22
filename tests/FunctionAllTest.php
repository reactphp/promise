<?php

namespace React\Promise;

class FunctionAllTest extends TestCase
{
    /** @test */
    public function shouldResolveEmptyInput()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo([]));

        all([])
            ->then($mock);
    }

    /** @test */
    public function shouldResolveValuesArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo([1, 2, 3]));

        all([1, 2, 3])
            ->then($mock);
    }

    /** @test */
    public function shouldResolvePromisesArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo([1, 2, 3]));

        all([resolve(1), resolve(2), resolve(3)])
            ->then($mock);
    }

    /** @test */
    public function shouldResolveSparseArrayInput()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo([null, 1, null, 1, 1]));

        all([null, 1, null, 1, 1])
            ->then($mock);
    }

    /** @test */
    public function shouldRejectIfAnyInputPromiseRejects()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(2));

        all([resolve(1), reject(2), resolve(3)])
            ->then($this->expectCallableNever(), $mock);
    }

    /** @test */
    public function shouldAcceptAPromiseForAnArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo([1, 2, 3]));

        all(resolve([1, 2, 3]))
            ->then($mock);
    }

    /** @test */
    public function shouldResolveToEmptyArrayWhenInputPromiseDoesNotResolveToArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo([]));

        all(resolve(1))
            ->then($mock);
    }

    /** @test */
    public function shouldPreserveTheOrderOfArrayWhenResolvingAsyncPromises()
    {
        $queue = new \SplQueue();
        $asyncPromise = new Promise(function ($resolve) use ($queue) {
            $queue->enqueue(function () use ($resolve, $queue) {
                $resolve(2);
            });
        });

        $result = null;

        all([resolve(1), $asyncPromise, resolve(3)])->then(function ($resolvedValues) use (&$result) {
            $result = $resolvedValues;
        });

        call_user_func($queue->dequeue());

        $this->assertSame([1, 2, 3], $result);
    }
}
