<?php

namespace React\Promise;

/**
 * Dummy attribute used to comment out code for PHP < 8 to ensure compatibility across test matrix
 *
 * @copyright Copyright (c) 2023 Christian Lück, taken from https://github.com/clue/framework-x with permission
 */
#[\Attribute]
class PHP8
{
    public function __construct()
    {
        assert(\PHP_VERSION_ID >= 80000);
    }
}
