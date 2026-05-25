<?php

require __DIR__.'/vendor/autoload.php';

use OpenApi\Generator;
use OpenApi\Analysers\TokenAnalyser;
use OpenApi\Analysers\ReflectionAnalyser;
use OpenApi\Analysers\DocBlockAnnotationFactory;
use OpenApi\Analysers\AttributeAnnotationFactory;

$generator = new Generator();
$generator->setAnalyser(new ReflectionAnalyser([
    new AttributeAnnotationFactory(),
    new DocBlockAnnotationFactory()
]));

$openapi = $generator->generate(['app/Http/Controllers/AuthController.php']);

echo $openapi->toJson();
