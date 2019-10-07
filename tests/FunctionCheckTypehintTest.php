<?php

namespace React\Promise;

use Exception;
use InvalidArgumentException;

class FunctionCheckTypehintTest extends TestCase
{
    /** @test */
    public function shouldAcceptClosureCallbackWithTypehint()
    {
        self::assertTrue(_checkTypehint(function (InvalidArgumentException $e) {}, new InvalidArgumentException()));
        self::assertFalse(_checkTypehint(function (InvalidArgumentException $e) {}, new Exception()));
    }

    /** @test */
    public function shouldAcceptFunctionStringCallbackWithTypehint()
    {
        self::assertTrue(_checkTypehint(new TestCallbackWithTypehintClass(), new InvalidArgumentException()));
        self::assertFalse(_checkTypehint(new TestCallbackWithTypehintClass(), new Exception()));
    }

    /** @test */
    public function shouldAcceptInvokableObjectCallbackWithTypehint()
    {
        self::assertTrue(_checkTypehint(new TestCallbackWithTypehintClass(), new InvalidArgumentException()));
        self::assertFalse(_checkTypehint(new TestCallbackWithTypehintClass(), new Exception()));
    }

    /** @test */
    public function shouldAcceptObjectMethodCallbackWithTypehint()
    {
        self::assertTrue(_checkTypehint([new TestCallbackWithTypehintClass(), 'testCallback'], new InvalidArgumentException()));
        self::assertFalse(_checkTypehint([new TestCallbackWithTypehintClass(), 'testCallback'], new Exception()));
    }

    /** @test */
    public function shouldAcceptStaticClassCallbackWithTypehint()
    {
        self::assertTrue(_checkTypehint([TestCallbackWithTypehintClass::class, 'testCallbackStatic'], new InvalidArgumentException()));
        self::assertFalse(_checkTypehint([TestCallbackWithTypehintClass::class, 'testCallbackStatic'], new Exception()));
    }

    /** @test */
    public function shouldAcceptClosureCallbackWithoutTypehint()
    {
        self::assertTrue(_checkTypehint(function (InvalidArgumentException $e) {
        }, new InvalidArgumentException()));
    }

    /** @test */
    public function shouldAcceptFunctionStringCallbackWithoutTypehint()
    {
        self::assertTrue(_checkTypehint(new TestCallbackWithTypehintClass(), new InvalidArgumentException()));
    }

    /** @test */
    public function shouldAcceptInvokableObjectCallbackWithoutTypehint()
    {
        self::assertTrue(_checkTypehint(new TestCallbackWithoutTypehintClass(), new InvalidArgumentException()));
    }

    /** @test */
    public function shouldAcceptObjectMethodCallbackWithoutTypehint()
    {
        self::assertTrue(_checkTypehint([new TestCallbackWithoutTypehintClass(), 'testCallback'], new InvalidArgumentException()));
    }

    /** @test */
    public function shouldAcceptStaticClassCallbackWithoutTypehint()
    {
        self::assertTrue(_checkTypehint([TestCallbackWithoutTypehintClass::class, 'testCallbackStatic'], new InvalidArgumentException()));
    }
}

function testCallbackWithTypehint(InvalidArgumentException $e)
{
}

function testCallbackWithoutTypehint()
{
}

class TestCallbackWithTypehintClass
{
    public function __invoke(InvalidArgumentException $e)
    {
    }

    public function testCallback(InvalidArgumentException $e)
    {
    }

    public static function testCallbackStatic(InvalidArgumentException $e)
    {
    }
}

class TestCallbackWithoutTypehintClass
{
    public function __invoke()
    {
    }

    public function testCallback()
    {
    }

    public static function testCallbackStatic()
    {
    }
}
