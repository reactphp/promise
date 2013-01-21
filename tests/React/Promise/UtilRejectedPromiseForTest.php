<?php

namespace React\Promise;

/**
 * @group Util
 * @group UtilRejectedPromiseFor
 */
class UtilRejectedPromiseForTest extends TestCase
{
    /** @test */
    public function shouldRejectWithAnImmediateValue()
    {
        $expected = 123;

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($expected));

        Util::rejectedPromiseFor($expected)
            ->then(
                $this->expectCallableNever(),
                $mock
            );
    }

    /** @test */
    public function shouldRejectWithFulfilledPromise()
    {
        $expected = 123;

        $resolved = new FulfilledPromise($expected);

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($expected));

        Util::rejectedPromiseFor($resolved)
            ->then(
                $this->expectCallableNever(),
                $mock
            );
    }

    /** @test */
    public function shouldRejectWithRejectedPromise()
    {
        $expected = 123;

        $resolved = new RejectedPromise($expected);

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($expected));

        Util::rejectedPromiseFor($resolved)
            ->then(
                $this->expectCallableNever(),
                $mock
            );
    }
}
