<?php

namespace React\Promise\PromiseTest;

trait AsyncInteropTestTrait
{
    /**
     * @return \React\Promise\PromiseAdapter\PromiseAdapterInterface
     */
    abstract public function getPromiseTestAdapter(callable $canceller = null);

    public function provideSuccessValues()
    {
        return [
            ["string"],
            [0],
            [PHP_INT_MAX],
            [defined('PHP_INT_MIN') ? PHP_INT_MIN : PHP_INT_MAX * -1],
            [-1.0],
            [true],
            [false],
            [[]],
            [null],
            [new \stdClass],
        ];
    }

    /** @dataProvider provideSuccessValues */
    public function testAsyncInteropPromiseSucceed($value)
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->promise()->when(function ($e, $v) use (&$invoked, $value) {
            $this->assertSame(null, $e);
            $this->assertSame($value, $v);
            $invoked = true;
        });
        $adapter->resolve($value);
        $this->assertTrue($invoked);
    }

    public function testSuccessAllWhensExecuted()
    {
        $adapter = $this->getPromiseTestAdapter();
        $invoked = 0;

        $adapter->promise()->when(function ($e, $v) use (&$invoked) {
            $this->assertSame(null, $e);
            $this->assertSame(true, $v);
            $invoked++;
        });
        $adapter->promise()->when(function ($e, $v) use (&$invoked) {
            $this->assertSame(null, $e);
            $this->assertSame(true, $v);
            $invoked++;
        });

        $adapter->resolve(true);

        $adapter->promise()->when(function ($e, $v) use (&$invoked) {
            $this->assertSame(null, $e);
            $this->assertSame(true, $v);
            $invoked++;
        });
        $adapter->promise()->when(function ($e, $v) use (&$invoked) {
            $this->assertSame(null, $e);
            $this->assertSame(true, $v);
            $invoked++;
        });

        $this->assertSame(4, $invoked);
    }

    public function testAsyncInteropPromiseExceptionFailure()
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->promise()->when(function ($e) use (&$invoked) {
            $this->assertSame(get_class($e), "RuntimeException");
            $invoked = true;
        });
        $adapter->reject(new \RuntimeException);
        $this->assertTrue($invoked);
    }

    public function testFailureAllWhensExecuted()
    {
        $adapter = $this->getPromiseTestAdapter();
        $invoked = 0;

        $adapter->promise()->when(function ($e) use (&$invoked) {
            $this->assertSame(get_class($e), "RuntimeException");
            $invoked++;
        });
        $adapter->promise()->when(function ($e) use (&$invoked) {
            $this->assertSame(get_class($e), "RuntimeException");
            $invoked++;
        });

        $adapter->reject(new \RuntimeException);

        $adapter->promise()->when(function ($e) use (&$invoked) {
            $this->assertSame(get_class($e), "RuntimeException");
            $invoked++;
        });
        $adapter->promise()->when(function ($e) use (&$invoked) {
            $this->assertSame(get_class($e), "RuntimeException");
            $invoked++;
        });

        $this->assertSame(4, $invoked);
    }

    public function testAsyncInteropPromiseErrorFailure()
    {
        if (PHP_VERSION_ID < 70000) {
            $this->markTestSkipped("Error only exists on PHP 7+");
        }

        $adapter = $this->getPromiseTestAdapter();

        $adapter->promise()->when(function ($e) use (&$invoked) {
            $this->assertSame(get_class($e), "Error");
            $invoked = true;
        });
        $adapter->reject(new \Error);
        $this->assertTrue($invoked);
    }
}
