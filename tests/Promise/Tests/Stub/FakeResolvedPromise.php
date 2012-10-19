<?php

namespace Promise\Tests\Stub;

class FakeResolvedPromise
{
    private $val;

    public function __construct($val = null)
    {
        $this->val = $val;
    }

    public function then($callback = null)
    {
        return new self($callback ? call_user_func($callback, $this->val) : $this->val);
    }
}
