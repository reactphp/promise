<?php

namespace React\Promise;

class DeferredTest extends TestCase
{
    use PromiseTest\FullTestTrait;

    public function getPromiseTestAdapter()
    {
        $d = new Deferred();

        return [
            'promise'  => [$d, 'promise'],
            'resolve'  => [$d, 'resolve'],
            'reject'   => [$d, 'reject'],
            'progress' => [$d, 'progress'],
        ];
    }
}
