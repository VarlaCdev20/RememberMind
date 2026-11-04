<?php

namespace App\Modulos\Identidad\Presentacion\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Documento;
use App\Modulos\Identidad\Presentacion\Policies\DocumentoLaboralPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class ArchivoDocumentalPersonalController extends Controller
{
    public function __invoke(Request $request, Documento $documento)
    {
        abort_unless(app(DocumentoLaboralPolicy::class)->view($request->user(), $documento), 403);
        $disk = str_starts_with($documento->ruta_archivo, 'documentos_personal_privados/') ? 'local' : 'public';
        $raiz = realpath(Storage::disk($disk)->path(''));
        $archivo = realpath(Storage::disk($disk)->path($documento->ruta_archivo));
        abort_unless($archivo && $raiz && str_starts_with($archivo, $raiz.DIRECTORY_SEPARATOR) && is_file($archivo), 404);
        $tipo = mime_content_type($archivo);
        $inline = in_array($tipo, ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'], true);

        return response()->file($archivo, ['Content-Type' => $inline ? $tipo : 'application/octet-stream',
            'Content-Disposition' => ($inline ? 'inline' : 'attachment').'; filename="'.Str::slug($documento->nombre ?: 'documento').'.'.pathinfo($archivo, PATHINFO_EXTENSION).'"',
            'X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store']);
    }
}
