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
    /** @test */
    public function shouldReturnAPromiseForAValue()
    {
        $result = Util::normalize(1);
        $this->assertInstanceOf('Promise\\ResolvedPromise', $result);
    }

    /** @test */
    public function shouldReturnAPromiseForAPromise()
    {
        $result = Util::normalize(new FakePromise());
        $this->assertInstanceOf('Promise\\ResolvedPromise', $result);
    }

    /** @test */
    public function shouldNotReturnTheInputPromise()
    {
        $fake = new FakePromise();

        $result = Util::normalize($fake, $this->identity());

        $this->assertInstanceOf('Promise\\ResolvedPromise', $result);
        $this->assertNotSame($result, $fake);
    }

    /** @test */
    public function shouldReturnAPromiseThatForwardsForAValue()
    {
        $result = Util::normalize(1, $this->constant(2));

        $this->assertInstanceOf('Promise\\ResolvedPromise', $result);

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with(2);

        $result->then($mock);
    }

    /** @test */
    public function shouldSupportDeepNestingInPromiseChains()
    {
        $d = new Deferred();
        $d->resolve(false);

        $result = Util::normalize(Util::normalize($d->then(function ($val) {
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

    /** @test */
    public function shouldReturnAResolvedPromiseForAResolvedInputPromise()
    {
        $result = Util::normalize(Util::resolve(true));

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(true));

        $result->then($mock);
    }

    /** @test */
    public function shouldAssimilateUntrustedPromises()
    {
        $untrusted = new FakePromise();
        $result = Util::normalize($untrusted);

        $this->assertNotSame($untrusted, $result);
    }

    /** @test */
    public function shouldAssimilateIntermediatePromisesAndForwardResults()
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
