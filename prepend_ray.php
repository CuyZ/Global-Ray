<?php

$globalRayAutoloader = __DIR__ . '/build/vendor/scoper-autoload.php';

if (file_exists($globalRayAutoloader)) {
    require_once $globalRayAutoloader;

    // This empties the global configuration used in Composer autoloader file
    // `autoload_real.php`. Without it, conflicts may appear with projects using
    // same dependencies as this package.
    // Inspired by https://github.com/phpstan/phpstan-src/commit/5e66c6c
    $GLOBALS['__composer_autoload_files'] = [];
}
