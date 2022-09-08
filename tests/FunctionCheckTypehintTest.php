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
        self::assertTrue(_checkTypehint(new CallbackWithTypehintClass(), new InvalidArgumentException()));
        self::assertFalse(_checkTypehint(new CallbackWithTypehintClass(), new Exception()));
    }

    /** @test */
    public function shouldAcceptInvokableObjectCallbackWithTypehint()
    {
        self::assertTrue(_checkTypehint(new CallbackWithTypehintClass(), new InvalidArgumentException()));
        self::assertFalse(_checkTypehint(new CallbackWithTypehintClass(), new Exception()));
    }

    /** @test */
    public function shouldAcceptObjectMethodCallbackWithTypehint()
    {
        self::assertTrue(_checkTypehint([new CallbackWithTypehintClass(), 'testCallback'], new InvalidArgumentException()));
        self::assertFalse(_checkTypehint([new CallbackWithTypehintClass(), 'testCallback'], new Exception()));
    }

    /** @test */
    public function shouldAcceptStaticClassCallbackWithTypehint()
    {
        self::assertTrue(_checkTypehint([CallbackWithTypehintClass::class, 'testCallbackStatic'], new InvalidArgumentException()));
        self::assertFalse(_checkTypehint([CallbackWithTypehintClass::class, 'testCallbackStatic'], new Exception()));
    }

    /**
     * @test
     * @requires PHP 8
     */
    public function shouldAcceptClosureCallbackWithUnionTypehint()
    {
        eval(
            'namespace React\Promise;' .
            'self::assertTrue(_checkTypehint(function (\RuntimeException|\InvalidArgumentException $e) {}, new \InvalidArgumentException()));' .
            'self::assertFalse(_checkTypehint(function (\RuntimeException|\InvalidArgumentException $e) {}, new \Exception()));'
        );
    }

    /**
     * @test
     * @requires PHP 8
     */
    public function shouldAcceptInvokableObjectCallbackWithUnionTypehint()
    {
        self::assertTrue(_checkTypehint(new CallbackWithUnionTypehintClass(), new InvalidArgumentException()));
        self::assertFalse(_checkTypehint(new CallbackWithUnionTypehintClass(), new Exception()));
    }

    /**
     * @test
     * @requires PHP 8
     */
    public function shouldAcceptObjectMethodCallbackWithUnionTypehint()
    {
        self::assertTrue(_checkTypehint([new CallbackWithUnionTypehintClass(), 'testCallback'], new InvalidArgumentException()));
        self::assertFalse(_checkTypehint([new CallbackWithUnionTypehintClass(), 'testCallback'], new Exception()));
    }

    /**
     * @test
     * @requires PHP 8
     */
    public function shouldAcceptStaticClassCallbackWithUnionTypehint()
    {
        self::assertTrue(_checkTypehint([CallbackWithUnionTypehintClass::class, 'testCallbackStatic'], new InvalidArgumentException()));
        self::assertFalse(_checkTypehint([CallbackWithUnionTypehintClass::class, 'testCallbackStatic'], new Exception()));
    }

    /**
     * @test
     * @requires PHP 8.1
     */
    public function shouldAcceptInvokableObjectCallbackWithIntersectionTypehint()
    {
        self::assertFalse(_checkTypehint(new CallbackWithIntersectionTypehintClass(), new \RuntimeException()));
        self::assertTrue(_checkTypehint(new CallbackWithIntersectionTypehintClass(), new CountableException()));
    }

    /**
     * @test
     * @requires PHP 8.1
     */
    public function shouldAcceptObjectMethodCallbackWithIntersectionTypehint()
    {
        self::assertFalse(_checkTypehint([new CallbackWithIntersectionTypehintClass(), 'testCallback'], new \RuntimeException()));
        self::assertTrue(_checkTypehint([new CallbackWithIntersectionTypehintClass(), 'testCallback'], new CountableException()));
    }

    /**
     * @test
     * @requires PHP 8.1
     */
    public function shouldAcceptStaticClassCallbackWithIntersectionTypehint()
    {
        self::assertFalse(_checkTypehint([CallbackWithIntersectionTypehintClass::class, 'testCallbackStatic'], new \RuntimeException()));
        self::assertTrue(_checkTypehint([CallbackWithIntersectionTypehintClass::class, 'testCallbackStatic'], new CountableException()));
    }

    /**
     * @test
     * @requires PHP 8.2
     */
    public function shouldAcceptInvokableObjectCallbackWithDNFTypehint()
    {
        self::assertFalse(_checkTypehint(new CallbackWithDNFTypehintClass(), new \RuntimeException()));
        self::assertTrue(_checkTypehint(new CallbackWithDNFTypehintClass(), new ArrayAccessibleException()));
        self::assertTrue(_checkTypehint(new CallbackWithDNFTypehintClass(), new CountableException()));
    }

    /**
     * @test
     * @requires PHP 8.2
     */
    public function shouldAcceptObjectMethodCallbackWithDNFTypehint()
    {
        self::assertFalse(_checkTypehint([new CallbackWithDNFTypehintClass(), 'testCallback'], new \RuntimeException()));
        self::assertTrue(_checkTypehint([new CallbackWithDNFTypehintClass(), 'testCallback'], new CountableException()));
        self::assertTrue(_checkTypehint([new CallbackWithDNFTypehintClass(), 'testCallback'], new ArrayAccessibleException()));
    }

    /**
     * @test
     * @requires PHP 8.2
     */
    public function shouldAcceptStaticClassCallbackWithDNFTypehint()
    {
        self::assertFalse(_checkTypehint([CallbackWithDNFTypehintClass::class, 'testCallbackStatic'], new \RuntimeException()));
        self::assertTrue(_checkTypehint([CallbackWithDNFTypehintClass::class, 'testCallbackStatic'], new CountableException()));
        self::assertTrue(_checkTypehint([CallbackWithDNFTypehintClass::class, 'testCallbackStatic'], new ArrayAccessibleException()));
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
        self::assertTrue(_checkTypehint(new CallbackWithoutTypehintClass(), new InvalidArgumentException()));
    }

    /** @test */
    public function shouldAcceptInvokableObjectCallbackWithoutTypehint()
    {
        self::assertTrue(_checkTypehint(new CallbackWithoutTypehintClass(), new InvalidArgumentException()));
    }

    /** @test */
    public function shouldAcceptObjectMethodCallbackWithoutTypehint()
    {
        self::assertTrue(_checkTypehint([new CallbackWithoutTypehintClass(), 'testCallback'], new InvalidArgumentException()));
    }

    /** @test */
    public function shouldAcceptStaticClassCallbackWithoutTypehint()
    {
        self::assertTrue(_checkTypehint([CallbackWithoutTypehintClass::class, 'testCallbackStatic'], new InvalidArgumentException()));
    }
}

function testCallbackWithTypehint(InvalidArgumentException $e)
{
}

function testCallbackWithoutTypehint()
{
}
