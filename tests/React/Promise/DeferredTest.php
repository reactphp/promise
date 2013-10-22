<?php

namespace React\Promise;

class DeferredTest extends TestCase
{
    use PromiseTest\FullTestTrait;

    public function getPromiseTestAdapter()
    {
        $d = new Deferred();

        return [
            'promise'  => $this->toClosure([$d, 'promise']),
            'resolve'  => $this->toClosure([$d, 'resolve']),
            'reject'   => $this->toClosure([$d, 'reject']),
            'progress' => $this->toClosure([$d, 'progress']),
        ];
    }
}
