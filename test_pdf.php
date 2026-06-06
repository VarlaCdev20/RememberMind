<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    \Spatie\LaravelPdf\Facades\Pdf::view('pdf.personal-institucional.ficha', ['data' => []])
        ->save(storage_path('app/public/test.pdf'));
    echo "OK Spatie\n";
} catch (\Exception $e) {
    echo "Spatie Failed: " . $e->getMessage() . "\n";
}

try {
    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.personal-institucional.ficha', ['data' => []]);
    \Illuminate\Support\Facades\Storage::disk('public')->put('test2.pdf', $pdf->output());
    echo "OK DomPDF\n";
} catch (\Exception $e) {
    echo "DomPDF Failed: " . $e->getMessage() . "\n";
}
