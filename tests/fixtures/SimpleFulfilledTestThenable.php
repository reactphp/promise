<?php

namespace React\Promise;

class SimpleFulfilledTestThenable
{
    public function then(?callable $onFulfilled = null, ?callable $onRejected = null): self
    {
        if ($onFulfilled) {
            $onFulfilled('foo');
        }

        return new self();
    }
}
