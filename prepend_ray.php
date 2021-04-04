<?php

$globalRayAutoloader = __DIR__ . '/build/vendor/scoper-autoload.php';

if (file_exists($globalRayAutoloader)) {
    require_once $globalRayAutoloader;
}
