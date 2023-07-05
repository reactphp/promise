<?php

namespace React\Promise;

use Exception;

class FunctionAllTest extends TestCase
{
    /** @test */
    public function shouldResolveEmptyInput(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke')
            ->with(self::identicalTo([]));

        all([])
            ->then($mock);
    }

    /** @test */
    public function shouldResolveValuesArray(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke')
            ->with(self::identicalTo([1, 2, 3]));

        all([1, 2, 3])
            ->then($mock);
    }

    /** @test */
    public function shouldResolvePromisesArray(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke')
            ->with(self::identicalTo([1, 2, 3]));

        all([resolve(1), resolve(2), resolve(3)])
            ->then($mock);
    }

    /** @test */
    public function shouldResolveSparseArrayInput(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke')
            ->with(self::identicalTo([null, 1, null, 1, 1]));

        all([null, 1, null, 1, 1])
            ->then($mock);
    }

    /** @test */
    public function shouldResolveValuesGenerator(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke')
            ->with(self::identicalTo([1, 2, 3]));

        $gen = (function () {
            for ($i = 1; $i <= 3; ++$i) {
                yield $i;
            }
        })();

        all($gen)->then($mock);
    }

    /** @test */
    public function shouldResolveValuesGeneratorEmpty(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke')
            ->with(self::identicalTo([]));

        $gen = (function () {
            if (false) { // @phpstan-ignore-line
                yield;
            }
        })();

        all($gen)->then($mock);
    }

    /** @test */
    public function shouldRejectIfAnyInputPromiseRejects(): void
    {
        $exception2 = new Exception();
        $exception3 = new Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke')
            ->with(self::identicalTo($exception2));

        all([resolve(1), reject($exception2), reject($exception3)])
            ->then($this->expectCallableNever(), $mock);
    }

    /** @test */
    public function shouldRejectInfiteGeneratorOrRejectedPromises(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke')
            ->with(new \RuntimeException('Iteration 1'));

        $gen = (function () {
            for ($i = 1; ; ++$i) {
                yield reject(new \RuntimeException('Iteration ' . $i));
            }
        })();

        all($gen)->then(null, $mock);
    }

    /** @test */
    public function shouldPreserveTheOrderOfArrayWhenResolvingAsyncPromises(): void
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects(self::once())
            ->method('__invoke')
            ->with(self::identicalTo([1, 2, 3]));

        $deferred = new Deferred();

        all([resolve(1), $deferred->promise(), resolve(3)])
            ->then($mock);

        $deferred->resolve(2);
    }
}
