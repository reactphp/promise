<?php

namespace React\Promise\PromiseTest;

trait AsyncInteropRejectedTestTrait
{
    /**
     * @return \React\Promise\PromiseAdapter\PromiseAdapterInterface
     */
    abstract public function getPromiseTestAdapter(callable $canceller = null);

    public function testWhenOnExceptionFailedAsyncInteropPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->reject(new \RuntimeException);
        $adapter->promise()->when(function ($e) use (&$invoked) {
            $this->assertSame(get_class($e), "RuntimeException");
            $invoked = true;
        });
        $this->assertTrue($invoked);
    }

    public function testWhenOnErrorFailedAsyncInteropPromise()
    {
        if (PHP_VERSION_ID < 70000) {
            $this->markTestSkipped("Error only exists on PHP 7+");
        }

        $adapter = $this->getPromiseTestAdapter();
        $adapter->reject(new \Error);
        $adapter->promise()->when(function ($e) use (&$invoked) {
            $this->assertSame(get_class($e), "Error");
            $invoked = true;
        });
        $this->assertTrue($invoked);
    }

    public function testWhenOnExceptionFailedAsyncInteropPromiseWhenRejectedWithoutReason()
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->reject();
        $adapter->promise()->when(function ($e, $value) use (&$invoked) {
            $this->assertInstanceOf("Exception", $e);
            $this->assertNull($value);
            $invoked = true;
        });
        $this->assertTrue($invoked);
    }
}
