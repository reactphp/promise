<?php

namespace React\Promise;

class CallbackWithoutTypehintClass
{
    public function __invoke(): void
    {
    }

    public function testCallback(): void
    {
    }

    public static function testCallbackStatic(): void
    {
    }
}
