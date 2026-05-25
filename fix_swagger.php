<?php
$dir = __DIR__ . '/app/Http/Controllers';
$files = glob($dir . '/*.php');

foreach ($files as $file) {
    $content = file_get_contents($file);
    if (strpos($content, '@OA\\') !== false && strpos($content, 'use OpenApi\\') === false) {
        $content = preg_replace('/(namespace App\\\\Http\\\\Controllers;)/', "$1\n\nuse OpenApi\\Annotations as OA;", $content);
        file_put_contents($file, $content);
        echo "Updated $file\n";
    }
}
echo "Done.\n";
