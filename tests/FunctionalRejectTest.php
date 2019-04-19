<?php

namespace React\Promise;

use stdClass;

class FunctionalRejectTest extends TestCase
{
    public function nonThrowables()
    {
        yield '1' => [1];
        yield 'true' => [true];
        yield 'stdClass' => [new stdClass()];
    }

    /**
     * @test
     * @dataProvider nonThrowables
     */
    public function shouldThrowWhenCalledWithANonException($input)
    {
        $errorCollector = new ErrorCollector();
        $errorCollector->start();

        (new Promise(function ($_, $reject) use ($input) {
            $reject($input);
        }))->done($this->expectCallableNever());

        $errors = $errorCollector->stop();

        $this->assertEquals(E_USER_ERROR, $errors[0]['errno']);
        $this->assertContains(
            'TypeError: Argument 1 passed to React\Promise\reject() must implement interface Throwable',
            $errors[0]['errstr']
        );
    }
}
