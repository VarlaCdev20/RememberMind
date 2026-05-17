<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $a = App\Models\AdultoMayor::first();
    $controller = app(App\Http\Controllers\Admin\AdultoMayorController::class);
    $request = Illuminate\Http\Request::create('/admin/adultos-mayores/' . $a->cod_am . '/reporte-individual', 'GET', ['format' => 'pdf']);
    $response = $controller->reporteIndividual($request, $a);
    echo "PDF generated successfully, type: " . get_class($response) . "\n";
} catch (\Exception $e) {
    echo "Error generating PDF: " . $e->getMessage() . "\n" . $e->getTraceAsString();
}

try {
    $request = Illuminate\Http\Request::create('/admin/adultos-mayores/reporte-general', 'GET', ['format' => 'pdf']);
    // Wait, is it reporte-general? Let's check route name.
} catch (\Exception $e) {
    echo "Error generating general report: " . $e->getMessage() . "\n";
}
