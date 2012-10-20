<?php

namespace Promise\Tests;

use Promise\RejectedPromise;
use Promise\ResolvedPromise;
use Promise\Util;

/**
 * @group Util
 * @group UtilResolve
 */
class UtilResolveTest extends TestCase
{
    /** @test */
    public function shouldResolveAnImmediateValue()
    {
        $expected = 123;

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($expected));

        Util::resolve($expected)
            ->then(
                $mock,
                $this->expectCallableNever()
            );
    }

    /** @test */
    public function shouldResolveAResolvedPromise()
    {
        $expected = 123;

        $resolved = new ResolvedPromise($expected);

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($expected));

        Util::resolve($resolved)
            ->then(
                $mock,
                $this->expectCallableNever()
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

        Util::resolve($resolved)
            ->then(
                $this->expectCallableNever(),
                $mock
            );
    }
}
