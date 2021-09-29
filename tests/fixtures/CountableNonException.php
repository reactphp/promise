<?php

namespace React\Promise;

use Countable;
use RuntimeException;

class CountableNonException implements Countable
{
    public function count()
    {
        return 0;
    }
}

