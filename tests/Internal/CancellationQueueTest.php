<?php

namespace React\Promise\Internal;

use Exception;
use React\Promise\Deferred;
use React\Promise\SimpleTestCancellable;
use React\Promise\SimpleTestCancellableThenable;
use React\Promise\TestCase;

class CancellationQueueTest extends TestCase
{
    /** @test */
    public function acceptsSimpleCancellableThenable(): void
    {
        $p = new SimpleTestCancellableThenable();

        $cancellationQueue = new CancellationQueue();
        $cancellationQueue->enqueue($p);

        $cancellationQueue();

        self::assertTrue($p->cancelCalled);
    }

    /** @test */
    public function ignoresSimpleCancellable(): void
    {
        $p = new SimpleTestCancellable();

        $cancellationQueue = new CancellationQueue();
        $cancellationQueue->enqueue($p);

        $cancellationQueue();

        self::assertFalse($p->cancelCalled);
    }

    /** @test */
    public function callsCancelOnPromisesEnqueuedBeforeStart(): void
    {
        $d1 = $this->getCancellableDeferred();
        $d2 = $this->getCancellableDeferred();

        $cancellationQueue = new CancellationQueue();
        $cancellationQueue->enqueue($d1->promise());
        $cancellationQueue->enqueue($d2->promise());

        $cancellationQueue();
    }

    /** @test */
    public function callsCancelOnPromisesEnqueuedAfterStart(): void
    {
        $d1 = $this->getCancellableDeferred();
        $d2 = $this->getCancellableDeferred();

        $cancellationQueue = new CancellationQueue();

        $cancellationQueue();

        $cancellationQueue->enqueue($d2->promise());
        $cancellationQueue->enqueue($d1->promise());
    }

    /** @test */
    public function doesNotCallCancelTwiceWhenStartedTwice(): void
    {
        $d = $this->getCancellableDeferred();

        $cancellationQueue = new CancellationQueue();
        $cancellationQueue->enqueue($d->promise());

        $cancellationQueue();
        $cancellationQueue();
    }

    /**
     * @test
     */
    public function rethrowsExceptionsThrownFromCancel(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('test');
        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke')
            ->will(self::throwException(new Exception('test')));

        $promise = new SimpleTestCancellableThenable($mock);

        $cancellationQueue = new CancellationQueue();
        $cancellationQueue->enqueue($promise);

        $cancellationQueue();
    }

    /**
     * @return Deferred<never>
     */
    private function getCancellableDeferred(): Deferred
    {
        /** @var Deferred<never> */
        return new Deferred($this->expectCallableOnce());
    }
}
