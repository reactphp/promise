<?php

namespace React\Promise;

use Exception;
use InvalidArgumentException;

define('UNION_TYPE_TESTS_ENABLED', defined('PHP_MAJOR_VERSION') && (PHP_MAJOR_VERSION >= 8));

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
    public function shouldAcceptClosureCallbackWithUnionTypehint()
    {
        if (UNION_TYPE_TESTS_ENABLED) {
            eval(
                'namespace React\Promise;' .
                'self::assertTrue(_checkTypehint(function (\RuntimeException|\InvalidArgumentException $e) {}, new \InvalidArgumentException()));' .
                'self::assertFalse(_checkTypehint(function (\RuntimeException|\InvalidArgumentException $e) {}, new \Exception()));'
            );
        } else {
            self::expectNotToPerformAssertions();
        }
    }

    /** @test */
    public function shouldAcceptInvokableObjectCallbackWithUnionTypehint()
    {
        if (UNION_TYPE_TESTS_ENABLED) {
            self::assertTrue(_checkTypehint(new TestCallbackWithUnionTypehintClass(), new InvalidArgumentException()));
            self::assertFalse(_checkTypehint(new TestCallbackWithUnionTypehintClass(), new Exception()));
        } else {
            self::expectNotToPerformAssertions();
        }
    }

    /** @test */
    public function shouldAcceptObjectMethodCallbackWithUnionTypehint()
    {
        if (UNION_TYPE_TESTS_ENABLED) {
            self::assertTrue(_checkTypehint([new TestCallbackWithUnionTypehintClass(), 'testCallback'], new InvalidArgumentException()));
            self::assertFalse(_checkTypehint([new TestCallbackWithUnionTypehintClass(), 'testCallback'], new Exception()));
        } else {
            self::expectNotToPerformAssertions();
        }
    }

    /** @test */
    public function shouldAcceptStaticClassCallbackWithUnionTypehint()
    {
        if (UNION_TYPE_TESTS_ENABLED) {
            self::assertTrue(_checkTypehint([TestCallbackWithUnionTypehintClass::class, 'testCallbackStatic'], new InvalidArgumentException()));
            self::assertFalse(_checkTypehint([TestCallbackWithUnionTypehintClass::class, 'testCallbackStatic'], new Exception()));
        } else {
            self::expectNotToPerformAssertions();
        }
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
        self::assertTrue(_checkTypehint(new TestCallbackWithoutTypehintClass(), new InvalidArgumentException()));
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

if (UNION_TYPE_TESTS_ENABLED) {
    eval(<<<EOT
namespace React\Promise;
class TestCallbackWithUnionTypehintClass
{
    public function __invoke(\RuntimeException|\InvalidArgumentException \$e)
    {
    }

    public function testCallback(\RuntimeException|\InvalidArgumentException \$e)
    {
    }

    public static function testCallbackStatic(\RuntimeException|\InvalidArgumentException \$e)
    {
    }
}
EOT
    );
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
