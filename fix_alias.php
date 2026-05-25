<?php
foreach(glob(__DIR__.'/app/Http/Controllers/*.php') as $file) {
    $c = file_get_contents($file);
    $c = str_replace('use OpenApi\Annotations as OA;', 'use OpenApi\Attributes as OA;', $c);
    file_put_contents($file, $c);
    echo "Fixed $file\n";
}
