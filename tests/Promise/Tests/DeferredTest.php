<?php

namespace Promise\Tests;

use Promise\Deferred;

class DeferredTest extends \PHPUnit_Framework_TestCase
{
    public function testResolve()
    {
        $d1 = new Deferred();

        $this->assertEquals(false, $d1->isResolved());
        $d1->resolve();
        $this->assertEquals(true, $d1->isResolved());

        $d2 = new Deferred();
        $d2->reject();
        $this->assertEquals(false, $d2->isResolved());
    }

    public function testReject()
    {
        $d1 = new Deferred();

        $this->assertEquals(false, $d1->isRejected());
        $d1->reject();
        $this->assertEquals(true, $d1->isRejected());

        $d2 = new Deferred();
        $d2->resolve();
        $this->assertEquals(false, $d2->isRejected());
    }

    public function testChaining()
    {
        $deferred = new Deferred();

        $self = $this;

        $deferred
            ->then(function ($value) {
                return $value + 2;
            })
            ->then(function ($value) use ($self) {
                $self->assertEquals(42, $value);
            });

        $deferred->resolve(42);
    }

    public function testChainingWithException()
    {
        $deferred = new Deferred();

        $self = $this;

        $deferred
            ->then(function () {
                throw new \Exception('Error has occured');
            })
            ->then(null, function ($e) use ($self) {
                $self->assertEquals('Error has occured', $e->getMessage());
            });

        $deferred->resolve();
    }
}
