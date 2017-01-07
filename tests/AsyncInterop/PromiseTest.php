<?php

namespace React\Promise\AsyncInterop;

use AsyncInterop\Promise\Test;
use React\Promise\Promise;

class PromiseTest extends Test
{
    public function promise(callable $canceller = null)
    {
        $resolveCallback = $rejectCallback = null;

        $promise = new Promise(function ($resolve, $reject) use (&$resolveCallback, &$rejectCallback) {
            $resolveCallback = $resolve;
            $rejectCallback  = $reject;
        }, $canceller);

        return [
            $promise,
            $resolveCallback,
            $rejectCallback
        ];
    }
}
