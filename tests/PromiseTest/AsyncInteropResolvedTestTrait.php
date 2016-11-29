<?php

namespace React\Promise\PromiseTest;

use Interop\Async\Promise as AsyncInteropPromise;

trait AsyncInteropResolvedTestTrait
{
    /**
     * @return \React\Promise\PromiseAdapter\PromiseAdapterInterface
     */
    abstract public function getPromiseTestAdapter(callable $canceller = null);

    public function provideResolvedSuccessValues()
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

    /** @dataProvider provideResolvedSuccessValues */
    public function testWhenOnSucceededAsyncInteropPromise($value)
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->resolve($value);
        $adapter->promise()->when(function ($e, $v) use (&$invoked, $value) {
            $this->assertSame(null, $e);
            $this->assertSame($value, $v);
            $invoked = true;
        });
        $this->assertTrue($invoked);
    }

    /**
     * Implementations MAY fail upon resolution with an AsyncInteropPromise,
     * but they definitely MUST NOT return an AsyncInteropPromise
     */
    public function testAsyncInteropPromiseResolutionWithAsyncInteropPromise()
    {
        $adapter = $this->getPromiseTestAdapter();
        $adapter->resolve(true);
        $success = $adapter->promise();

        $adapter = $this->getPromiseTestAdapter();

        $ex = false;
        try {
            $adapter->resolve($success);
        } catch (\Throwable $e) {
            $ex = true;
        } catch (\Exception $e) {
            $ex = true;
        }
        if (!$ex) {
            $adapter->promise()->when(function ($e, $v) use (&$invoked) {
                $invoked = true;
                $this->assertFalse($v instanceof AsyncInteropPromise);
            });
            $this->assertTrue($invoked);
        }
    }
}
