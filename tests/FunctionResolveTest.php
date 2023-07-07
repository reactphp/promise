<?php

namespace React\Promise;

use React\Promise\Internal\FulfilledPromise;
use React\Promise\Internal\RejectedPromise;
use Exception;

class FunctionResolveTest extends TestCase
{
    /** @test */
    public function shouldResolveAnImmediateValue(): void
    {
        $expected = 123;

        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke')
            ->with(self::identicalTo($expected));

        resolve($expected)
            ->then(
                $mock,
                $this->expectCallableNever()
            );
    }

    /** @test */
    public function shouldResolveAFulfilledPromise(): void
    {
        $expected = 123;

        $resolved = new FulfilledPromise($expected);

        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke')
            ->with(self::identicalTo($expected));

        resolve($resolved)
            ->then(
                $mock,
                $this->expectCallableNever()
            );
    }

    /** @test */
    public function shouldResolveAThenable(): void
    {
        $thenable = new SimpleFulfilledTestThenable();

        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke')
            ->with(self::identicalTo('foo'));

        resolve($thenable)
            ->then(
                $mock,
                $this->expectCallableNever()
            );
    }

    /** @test */
    public function shouldResolveACancellableThenable(): void
    {
        $thenable = new SimpleTestCancellableThenable();

        $promise = resolve($thenable);
        $promise->cancel();

        self::assertTrue($thenable->cancelCalled);
    }

    /** @test */
    public function shouldRejectARejectedPromise(): void
    {
        $exception = new Exception();

        $resolved = new RejectedPromise($exception);

        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke')
            ->with(self::identicalTo($exception));

        resolve($resolved)
            ->then(
                $this->expectCallableNever(),
                $mock
            );
    }

    /** @test */
    public function shouldSupportDeepNestingInPromiseChains(): void
    {
        $d = new Deferred();
        $d->resolve(false);

        $result = resolve(resolve($d->promise()->then(function ($val) {
            $d = new Deferred();
            $d->resolve($val);

            $identity = function ($val) {
                return $val;
            };

            return resolve($d->promise()->then($identity))->then(
                function ($val) {
                    return !$val;
                }
            );
        })));

        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke')
            ->with(self::identicalTo(true));

        $result->then($mock);
    }

    /** @test */
    public function shouldSupportVeryDeepNestedPromises(): void
    {
        if (PHP_VERSION_ID < 70200 && ini_get('xdebug.max_nesting_level') !== false) {
            $this->markTestSkipped('Skip unhandled rejection on legacy PHP 7.1');
        }

        $deferreds = [];

        for ($i = 0; $i < 150; $i++) {
            $deferreds[] = $d = new Deferred();
            $p = $d->promise();

            $last = $p;
            for ($j = 0; $j < 150; $j++) {
                $last = $last->then(function ($result) {
                    return $result;
                });
            }
        }

        $p = null;
        foreach ($deferreds as $d) {
            if ($p) {
                $d->resolve($p);
            }

            $p = $d->promise();
        }

        $deferreds[0]->resolve(true);

        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke')
            ->with(self::identicalTo(true));

        $deferreds[0]->promise()->then($mock);
    }
}
