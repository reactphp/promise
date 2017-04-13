<?php

namespace React\Promise\Internal;

use React\Promise\Deferred;
use React\Promise\SimpleTestCancellable;
use React\Promise\SimpleTestCancellableThenable;
use React\Promise\TestCase;

class CancellationQueueTest extends TestCase
{
    /** @test */
    public function acceptsSimpleCancellableThenable()
    {
        $p = new SimpleTestCancellableThenable();

        $cancellationQueue = new CancellationQueue();
        $cancellationQueue->enqueue($p);

        $cancellationQueue();

        $this->assertTrue($p->cancelCalled);
    }

    /** @test */
    public function ignoresSimpleCancellable()
    {
        $p = new SimpleTestCancellable();

        $cancellationQueue = new CancellationQueue();
        $cancellationQueue->enqueue($p);

        $cancellationQueue();

        $this->assertFalse($p->cancelCalled);
    }

    /** @test */
    public function callsCancelOnPromisesEnqueuedBeforeStart()
    {
        $d1 = $this->getCancellableDeferred();
        $d2 = $this->getCancellableDeferred();

        $cancellationQueue = new CancellationQueue();
        $cancellationQueue->enqueue($d1->promise());
        $cancellationQueue->enqueue($d2->promise());

        $cancellationQueue();
    }

    /** @test */
    public function callsCancelOnPromisesEnqueuedAfterStart()
    {
        $d1 = $this->getCancellableDeferred();
        $d2 = $this->getCancellableDeferred();

        $cancellationQueue = new CancellationQueue();

        $cancellationQueue();

        $cancellationQueue->enqueue($d2->promise());
        $cancellationQueue->enqueue($d1->promise());
    }

    /** @test */
    public function doesNotCallCancelTwiceWhenStartedTwice()
    {
        $d = $this->getCancellableDeferred();

        $cancellationQueue = new CancellationQueue();
        $cancellationQueue->enqueue($d->promise());

        $cancellationQueue();
        $cancellationQueue();
    }

    /** @test */
    public function rethrowsExceptionsThrownFromCancel()
    {
        $this->setExpectedException('\Exception', 'test');

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->will($this->throwException(new \Exception('test')));

        $promise = new SimpleTestCancellableThenable($mock);

        $cancellationQueue = new CancellationQueue();
        $cancellationQueue->enqueue($promise);

        $cancellationQueue();
    }

    private function getCancellableDeferred()
    {
        return new Deferred($this->expectCallableOnce());
    }
}
