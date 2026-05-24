<?php
require 'vendor/autoload.php';

$dir = 'app/Http/Controllers';
$files = glob($dir . '/*.php');
$errorFile = null;

$generator = new \OpenApi\Generator();

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
}, E_USER_WARNING | E_USER_NOTICE);

foreach ($files as $file) {
    if (basename($file) === 'Controller.php') continue;
    try {
        $openapi = $generator->generate([$dir . '/Controller.php', $file]);
    } catch (\Throwable $e) {
        echo "Error in file: $file\n";
        echo $e->getMessage() . "\n";
        $errorFile = $file;
        break;
    }
}
if (!$errorFile) {
    echo "No errors found!\n";
}
