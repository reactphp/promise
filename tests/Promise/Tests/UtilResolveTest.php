<?php

namespace Promise\Tests;

use Promise\Deferred;
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

    /** @test */
    public function shouldSupportDeepNestingInPromiseChains()
    {
        $d = new Deferred();
        $d->resolve(false);

        $result = Util::resolve(Util::resolve($d->then(function ($val) {
            $d = new Deferred();
            $d->resolve($val);

            $identity = function ($val) {
                return $val;
            };

            return Util::resolve($d->then($identity))->then(
                function ($val) {
                    return !$val;
                }
            );
        })));

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(true));

        $result->then($mock);
    }
}
