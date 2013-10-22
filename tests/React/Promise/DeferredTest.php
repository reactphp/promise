<?php

namespace React\Promise;

class DeferredTest extends TestCase
{
    use PromiseTest\FullTestTrait;

    public function getPromiseTestAdapter()
    {
        $d = new Deferred();

        return [
            'promise'  => $this->toClosure(array($d, 'promise')),
            'resolve'  => $this->toClosure(array($d, 'resolve')),
            'reject'   => $this->toClosure(array($d, 'reject')),
            'progress' => $this->toClosure(array($d, 'progress')),
        ];
    }
}
