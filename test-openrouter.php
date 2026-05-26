<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$openRouter = app(\App\Services\OpenRouterService::class);
echo "Testing connection to OpenRouter (Model: nvidia/llama-3.1-nemotron-70b-instruct)...\n\n";

$start = microtime(true);
$response = $openRouter->generate('Katakan halo dengan sangat singkat!');
$time = microtime(true) - $start;

echo "Response:\n";
echo "---------\n";
echo $response . "\n";
echo "---------\n";
echo "Time taken: " . round($time, 2) . " seconds\n";
