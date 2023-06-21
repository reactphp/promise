<?php

namespace React\Promise;

use InvalidArgumentException;
use RuntimeException;

class CallbackWithUnionTypehintClass
{
    #[PHP8] public function __invoke(RuntimeException|InvalidArgumentException $e): void { }/*
    public function __invoke(bool $unusedOnPhp8ButRequiredToMakePhpstanWorkOnLegacyPhp = true): void { } // */

    #[PHP8] public function testCallback(RuntimeException|InvalidArgumentException $e): void { }/*
    public function testCallback(bool $unusedOnPhp8ButRequiredToMakePhpstanWorkOnLegacyPhp = true): void { } // */

    #[PHP8] public static function testCallbackStatic(RuntimeException|InvalidArgumentException $e): void { }/*
    public static function testCallbackStatic(bool $unusedOnPhp8ButRequiredToMakePhpstanWorkOnLegacyPhp = true): void { } // */
}
