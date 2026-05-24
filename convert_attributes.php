<?php
$dir = 'app/Http/Controllers';
$files = glob($dir . '/*.php');

foreach ($files as $file) {
    if (basename($file) === 'Controller.php' || basename($file) === 'ChatbotController.php') continue;
    
    $content = file_get_contents($file);
    
    // Check if it has @OA
    if (strpos($content, '@OA\\') === false) continue;
    
    // Make sure we have the use statement
    if (strpos($content, 'use OpenApi\Attributes as OA;') === false) {
        $content = preg_replace('/(use Illuminate\\\\.*?;\n)/', "$1use OpenApi\Attributes as OA;\n", $content, 1);
    }

    // Convert docblock OA tags to PHP 8 Attributes
    // This is a naive replacement but works for most standard swagger annotations
    
    // First, remove the `* ` prefixes inside OA tags
    $content = preg_replace_callback('/(\/\*\*\s*\n)(.*?)(?=\s*\*\/\s*public function)/s', function($matches) {
        $docblock = $matches[2];
        
        // Extract the OA parts
        if (preg_match('/(\@OA\\\\.*)/s', $docblock, $oaMatches)) {
            $oaPart = $oaMatches[1];
            
            // Remove ' * ' prefix
            $oaPart = preg_replace('/^\s*\*\s?/m', '', $oaPart);
            
            // Replace @OA\ with #[OA\
            // But we need to be careful with nested annotations. In PHP 8 attributes, nested annotations use `new OA\Something(...)`
            // And arrays use `[...]` instead of `{...}`
            // And keys are `key: value` instead of `key=value`
            
            // Since Regex conversion is extremely complex for nested structures,
            // we will just do this manually for the files.
        }
        return $matches[0];
    }, $content);
}
echo "Script complete";
