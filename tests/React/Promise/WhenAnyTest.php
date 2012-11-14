<?php

namespace React\Promise;

/**
 * @group When
 * @group WhenAny
 */
class WhenAnyTest extends TestCase
{
    /** @test */
    public function shouldResolveToNullWithEmptyInputArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(null));

        When::any(array(), $mock);
    }

    /** @test */
    public function shouldResolveWithAnInputValue()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        When::any(
            array(1, 2, 3),
            $mock
        );
    }

    /** @test */
    public function shouldResolveWithAPromisedInputValue()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        When::any(
            array(When::resolve(1), When::resolve(2), When::resolve(3)),
            $mock
        );
    }

    /** @test */
    public function shouldRejectWithAllRejectedInputValuesIfAllInputsAreRejected()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(array(0 => 1, 1 => 2, 2 => 3)));

        When::any(
            array(When::reject(1), When::reject(2), When::reject(3)),
            $this->expectCallableNever(),
            $mock
        );
    }

    /** @test */
    public function shouldResolveWhenFirstInputPromiseResolves()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        When::any(
            array(When::resolve(1), When::reject(2), When::reject(3)),
            $mock
        );
    }

    /** @test */
    public function shouldAcceptAPromiseForAnArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        When::any(
            When::resolve(array(1, 2, 3)),
            $mock
        );
    }

    /** @test */
    public function shouldResolveToNullArrayWhenInputPromiseDoesNotResolveToArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(null));

        When::any(
            When::resolve(1),
            $mock
        );
    }

    /** @test */
    public function shouldNotRelyOnArryIndexesWhenUnwrappingToASingleResolutionValue()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(2));

        $d1 = new Deferred();
        $d2 = new Deferred();

        When::any(
            array('abc' => $d1->promise(), 1 => $d2->promise()),
            $mock
        );

        $d2->resolve(2);
        $d1->resolve(1);
    }
}
