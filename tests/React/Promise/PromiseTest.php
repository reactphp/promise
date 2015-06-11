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
        $promise = new Promise(function($_, $reject) {
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
    public function shouldProgres()
    {
        $promise = new Promise(function($_, $_, $progress) use (&$notify) {
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
}