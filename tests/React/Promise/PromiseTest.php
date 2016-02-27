<?php

namespace React\Promise;

/**
 * @group Promise
 */
class PromiseTest extends TestCase
{
    /** @test */
    public function shouldThrowIfResolverIsNotACallable()
    {
        $this->setExpectedException('\InvalidArgumentException');

        new Promise(null);
    }
    /** @test */
    public function shouldRejectIfResolverThrows()
    {
        $e = new \Exception();

        $promise = new Promise(function() use($e) {
            throw $e;
        });

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($e));

        $promise->then($this->expectCallableNever(), $mock);
    }

    /** @test */
    public function shouldResolve()
    {
        $promise = new Promise(function($resolve) {
            $resolve(1);
        });

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $promise->then($mock);
    }

    /** @test */
    public function shouldReject()
    {
        $promise = new Promise(function($resolve, $reject) {
            $reject(1);
        });

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $promise->then($this->expectCallableNever(), $mock);
    }

    /** @test */
    public function shouldProgress()
    {
        $promise = new Promise(function($resolve, $reject, $progress) use (&$notify) {
            $notify = $progress;
        });

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $promise->then($this->expectCallableNever(), $this->expectCallableNever(), $mock);

        $notify(1);
    }

    /** @test */
    public function shouldInvokeCancellationHandlerAndStayPendingWhenCallingCancel()
    {
        $promise = new Promise(function() { }, $this->expectCallableOnce());
        $promise->cancel();

        $promise->then($this->expectCallableNever(), $this->expectCallableNever());
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function shouldThrowIfCancellerIsNotACallable()
    {
        new Promise(function () { }, false);
    }
}
