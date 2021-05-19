<?php

use function React\Promise\reject;

require dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

reject(new Exception('Boom!'))->then(null, static function () {
    exit(0);
});

exit(2);
