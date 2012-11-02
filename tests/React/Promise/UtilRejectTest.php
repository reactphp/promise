<?php

namespace React\Promise;

/**
 * @group Util
 * @group UtilReject
 */
class UtilRejectTest extends TestCase
{
    /** @test */
    public function shouldRejectAnImmediateValue()
    {
        $expected = 123;

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($expected));

        Util::reject($expected)
            ->then(
                $this->expectCallableNever(),
                $mock
            );
    }

    /** @test */
    public function shouldRejectAFulfilledPromise()
    {
        $expected = 123;

        $resolved = new FulfilledPromise($expected);

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($expected));

        Util::reject($resolved)
            ->then(
                $this->expectCallableNever(),
                $mock
            );
    }

    /** @test */
    public function shouldRejectARejectedPromise()
    {
        $expected = 123;

        $resolved = new RejectedPromise($expected);

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($expected));

        Util::reject($resolved)
            ->then(
                $this->expectCallableNever(),
                $mock
            );
    }
}
