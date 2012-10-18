<?php

namespace Promise\Tests;

use Promise\Promise;

class PromiseTest extends \PHPUnit_Framework_TestCase
{
    public function testWhen()
    {
        $def1 = Promise::defer();

        $def2 = Promise::defer()->resolve(2);

        $var = 3;

        $func = function () {
            return 4;
        };

        $self = $this;

        Promise::when(array($def1, $def2, $var, $func))
            ->then(function ($results) use ($self) {
                $self->assertCount(4, $results);

                $self->assertEquals(1, array_shift($results));
                $self->assertEquals(2, array_shift($results));
                $self->assertEquals(3, array_shift($results));
                $self->assertEquals(4, array_shift($results));
            });

        $def1->resolve(1);
    }

    public function testWhenWithException()
    {
        $d3 = Promise::defer();
        $d3->then(function() {
            throw new \Exception('Error has occured');
        });

        $self = $this;

        Promise::when(array($d3))
            ->then(
                function () {
                },
                function ($e) use ($self) {
                    $self->assertEquals('Error has occured', $e->getMessage());
                }
            );

        $d3->resolve();
    }

    public function testWhenWithNestedPromise()
    {
        $def = Promise::defer();

        $func = function () {
            return Promise::defer()->resolve(2)->promise();
        };

        $self = $this;

        Promise::when(array($def, $func))
            ->then(function ($results) use ($self) {
                $self->assertCount(2, $results);

                $self->assertEquals(1, array_shift($results));
                $self->assertEquals(2, array_shift($results));
            });

        $def->resolve(1);
    }
}
