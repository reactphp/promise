<?php

namespace React\Promise\AsyncInterop;

use AsyncInterop\Promise\Test;
use React\Promise\Deferred;

class DeferredTest extends Test
{
    public function promise(callable $canceller = null)
    {
        $d = new Deferred($canceller);

        return [
            $d->promise(),
            [$d, 'resolve'],
            [$d, 'reject']
        ];
    }
}
