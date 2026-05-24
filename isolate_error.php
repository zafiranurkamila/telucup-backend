<?php
require 'vendor/autoload.php';

$generator = new \OpenApi\Generator();

try {
    $openapi = $generator->generate(['app/Http/Controllers/Controller.php', 'app/Http/Controllers/CampaignController.php']);
    echo "CampaignController is OK.\n";
} catch (\Throwable $e) {
    echo "Error in CampaignController\n";
    echo $e->getMessage() . "\n";
}
