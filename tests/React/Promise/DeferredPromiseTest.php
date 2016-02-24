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
        $mock = $this->getMock('React\\Promise\\Deferred');
        $mock
            ->expects($this->once())
            ->method('then')
            ->with(1, 2, 3);

        $p = new DeferredPromise($mock);
        $p->then(1, 2, 3);
    }

    /** @test */
    public function shouldForwardCancelToDeferred()
    {
        $mock = $this->getMock('React\\Promise\\Deferred');
        $mock
            ->expects($this->once())
            ->method('cancel');

        $p = new DeferredPromise($mock);
        $p->cancel();
    }
}
