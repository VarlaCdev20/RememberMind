<?php

namespace App\Modulos\Cuidados\Presentacion\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DocumentoClinico;
use App\Models\Residente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class FotografiaHeridaController extends Controller
{
    public function __invoke(Request $request, Residente $residente, DocumentoClinico $documentoClinico): StreamedResponse
    {
        Gate::authorize('consultar-evidencia-herida', [$documentoClinico, $residente]);
        abort_unless(array_key_exists($documentoClinico->formato, config('fotografias_clinicas.formatos'))
            && str_starts_with($documentoClinico->ruta_archivo, 'clinical/residents/'.$residente->getKey().'/wounds/')
            && ! str_contains($documentoClinico->ruta_archivo, '..'), 404);
        $disk = Storage::disk(config('fotografias_clinicas.disk'));
        abort_unless($disk->exists($documentoClinico->ruta_archivo), 404);
        $extension = config('fotografias_clinicas.formatos')[$documentoClinico->formato];
        $headers = ['Content-Type' => $documentoClinico->formato, 'Cache-Control' => 'private, no-store, max-age=0', 'X-Content-Type-Options' => 'nosniff', 'Content-Security-Policy' => "default-src 'none'; sandbox"];

        return $request->boolean('descargar')
            ? $disk->download($documentoClinico->ruta_archivo, 'fotografia-clinica.'.$extension, $headers)
            : $disk->response($documentoClinico->ruta_archivo, 'fotografia-clinica.'.$extension, $headers, 'inline');
    }
}
