<?php

namespace Promise\Tests\Stub;

class FakePromise
{
    private $val;

    public function __construct($val = null)
    {
        $this->val = $val;
    }

    public function then($callback = null)
    {
        if ($callback) {
            call_user_func($callback, $this->val);
        }

        return $this;
    }
}
