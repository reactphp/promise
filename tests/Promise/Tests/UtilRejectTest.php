<?php

namespace Promise\Tests;

use Promise\Deferred;
use Promise\RejectedPromise;
use Promise\ResolvedPromise;
use Promise\Util;

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
    public function shouldRejectAResolvedPromise()
    {
        $expected = 123;

        $resolved = new ResolvedPromise($expected);

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
