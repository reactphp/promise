<?php

namespace React\Promise;

use Countable;
use RuntimeException;

class CountableException extends RuntimeException implements Countable
{
    #[\ReturnTypeWillChange]
    public function count()
    {
        return 0;
    }
}

