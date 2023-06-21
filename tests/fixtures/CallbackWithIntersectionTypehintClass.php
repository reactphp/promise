<?php

namespace React\Promise;

use Countable;
use RuntimeException;

class CallbackWithIntersectionTypehintClass
{
    #[PHP8] public function __invoke(RuntimeException&Countable $e): void { }/*
    public function __invoke(bool $unusedOnPhp8ButRequiredToMakePhpstanWorkOnLegacyPhp = true): void { } // */

    #[PHP8] public function testCallback(RuntimeException&Countable $e): void { }/*
    public function testCallback(bool $unusedOnPhp8ButRequiredToMakePhpstanWorkOnLegacyPhp = true): void { } // */

    #[PHP8] public static function testCallbackStatic(RuntimeException&Countable $e): void { }/*
    public static function testCallbackStatic(bool $unusedOnPhp8ButRequiredToMakePhpstanWorkOnLegacyPhp = true): void { } // */
}
