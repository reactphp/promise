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
            ->then(function ($resultFromDef1, $resultFromDef2, $resultFromVar, $resultFromFunc) use ($self) {
                $self->assertEquals(1, $resultFromDef1);
                $self->assertEquals(2, $resultFromDef2);
                $self->assertEquals(3, $resultFromVar);
                $self->assertEquals(4, $resultFromFunc);
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
            ->then(function ($resultFromDef, $resultFromFunc) use ($self) {
                $self->assertEquals(1, $resultFromDef);
                $self->assertEquals(2, $resultFromFunc);
            });

        $def->resolve(1);
    }
}
