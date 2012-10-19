<?php

namespace Promise\Tests;

use Promise\Deferred;
use Promise\Util;
use Promise\Tests\Stub\FakePromise;

/**
 * @group Util
 * @group UtilNormalize
 */
class UtilNormalizeTest extends TestCase
{
    public function testShouldReturnAPromiseForAValue()
    {
        $result = Util::normalize(1);
        $this->assertInstanceOf('Promise\\Promise', $result);
    }

    public function testShouldReturnAPromiseForAPromise()
    {
        $result = Util::normalize(new FakePromise());
        $this->assertInstanceOf('Promise\\Promise', $result);
    }

    public function testShouldNotReturnTheInputPromise()
    {
        $fake = new FakePromise();

        $result = Util::normalize($fake, $this->identity());

        $this->assertInstanceOf('Promise\\Promise', $result);
        $this->assertNotSame($result, $fake);
    }

    public function testShouldReturnAPromiseThatForwardsForAValue()
    {
        $result = Util::normalize(1, $this->constant(2));

        $this->assertInstanceOf('Promise\\Promise', $result);

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with(2);

        $result->then($mock);
    }

    public function testShouldSupportDeepNestingInPromiseChains()
    {
        $d = new Deferred();
        $d->resolve(false);

        $result = Util::normalize(Util::normalize($d->then(function($val) {
            $d = new Deferred();
            $d->resolve($val);

            return Util::normalize($d->then($this->identity()), $this->identity())->then(
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

    public function testShouldReturnAResolvedPromiseForAResolvedInputPromise()
    {
        $result = Util::normalize(Util::resolve(true));

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(true));

        $result->then($mock);
    }

    public function testShouldReturnAResolvedPromiseForAResolvedFunctionPromise()
    {
        $result = Util::normalize(Util::resolve($this->constant(true)));

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(true));

        $result->then($mock);
    }

    public function testShouldAssimilateUntrustedPromises()
    {
        $untrusted = new FakePromise();
        $result = Util::normalize($untrusted);

        $this->assertNotSame($untrusted, $result);
    }

    public function testShouldAssimilateIntermediatePromisesAndForwardResults()
    {
        $untrusted = new FakePromise(1);
        $result = Util::normalize($untrusted, function ($val) {
            return new FakePromise($val + 1);
        });

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(2))
            ->will($this->returnValue(new FakePromise(3)));

        $mock2 = $this->createCallableMock();
        $mock2
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(3));

        Util::normalize($result)
            ->then($mock)
            ->then($mock2);
    }
}
