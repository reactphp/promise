<?php

namespace React\Promise\AsyncInterop;

use AsyncInterop\Promise\Test;
use React\Promise\Deferred;
use React\Promise\LazyPromise;

class LazyPromiseTest extends Test
{
    public function promise(callable $canceller = null)
    {
        $d = new Deferred($canceller);

        $factory = function () use ($d) {
            return $d->promise();
        };

        return [
            new LazyPromise($factory),
            [$d, 'resolve'],
            [$d, 'reject']
        ];
    }
}
