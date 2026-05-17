<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Mock authentication
$user = App\Models\User::first();
Auth::login($user);

$a = App\Models\AdultoMayor::first();
$url = '/admin/adultos-mayores/' . $a->cod_am . '/reporte-individual?format=pdf';
echo "Testing: $url\n";

$request = Illuminate\Http\Request::create($url, 'GET');
$response = $kernel->handle($request);

echo "Status: " . $response->getStatusCode() . "\n";
if ($response->getStatusCode() == 200) {
    echo "PDF or Response generated successfully. Size: " . strlen($response->getContent()) . " bytes\n";
} else {
    echo "Error: \n";
    echo substr(strip_tags($response->getContent()), 0, 500);
}
