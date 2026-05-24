<?php require 'vendor/autoload.php'; $openapi = \OpenApi\Generator::scan(['app/Http/Controllers']); echo $openapi->toJson();
