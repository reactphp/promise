<?php

namespace React\Promise;

/**
 * @group Promise
 * @group DeferredPromise
 */
class DeferredPromiseTest extends TestCase
{
    /** @test */
    public function shouldForwardToDeferred()
    {
        $callable = $this->createCallableMock();

        $mock = $this->getMock('React\\Promise\\Deferred');
        $mock
            ->expects($this->once())
            ->method('then')
            ->with($callable, $callable, $callable);

        $p = new DeferredPromise($mock);
        $p->then($callable, $callable, $callable);
    }
}
