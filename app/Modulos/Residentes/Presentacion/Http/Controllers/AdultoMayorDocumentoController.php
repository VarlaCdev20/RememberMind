<?php

namespace App\Modulos\Residentes\Presentacion\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Residente;
use App\Modulos\Residentes\Aplicacion\Acciones\GestionarDocumentosResidenteHttp;
use App\Modulos\Residentes\Presentacion\Http\Requests\StoreDocumentoAdultoRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdultoMayorDocumentoController extends Controller
{
    public function index(Residente $adulto_mayor)
    {
        $this->authorize('view', $adulto_mayor);
        abort_if(auth()->user()->hasRole('FAMILIAR'), 403, 'Estos documentos no están publicados para familiares.');
        $adulto_mayor->load(['documentos' => function ($q) {
            $q->orderByDesc('cod_documento');
        }]);

        return view('pages.adultos-mayores.documentos.index', compact('adulto_mayor'));
    }

    public function store(StoreDocumentoAdultoRequest $request, Residente $adulto_mayor)
    {
        app(GestionarDocumentosResidenteHttp::class)->store(auth()->user(), $request->validated(), $adulto_mayor);

        return redirect()->route('admin.adultos-mayores.documentos.index', $adulto_mayor->cod_residente)->with('success', 'Documento subido correctamente.');
    }

    public function archivo(Request $request, Residente $adulto_mayor, string $documento)
    {
        $this->authorize('view', $adulto_mayor);
        abort_if($request->user()->hasRole('FAMILIAR'), 403, 'Estos documentos no están publicados para familiares.');
        $doc = $adulto_mayor->documentos()->findOrFail($documento);
        $disco = Storage::disk('local');
        if (! $disco->exists($doc->ruta_archivo)) {
            $disco = Storage::disk('public');
        }
        abort_unless($disco->exists($doc->ruta_archivo), 404, 'Archivo no disponible.');
        if ($request->has('ver') || $request->has('preview') || $request->has('inline')) {
            $path = $disco->path($doc->ruta_archivo);
            $mime = $disco->mimeType($doc->ruta_archivo) ?: 'application/pdf';

            return response()->file($path, ['Content-Type' => $mime]);
        }

        return $disco->download($doc->ruta_archivo);
    }

    public function update(Request $request, Residente $adulto_mayor, $documento)
    {
        app(GestionarDocumentosResidenteHttp::class)->update(auth()->user(), $request->all(), $adulto_mayor, $documento);

        return redirect()->route('admin.adultos-mayores.documentos.index', $adulto_mayor->cod_residente)->with('success', 'Metadatos del documento actualizados.');
    }

    public function destroy(Residente $adulto_mayor, $documento)
    {
        app(GestionarDocumentosResidenteHttp::class)->destroy(auth()->user(), [], $adulto_mayor, $documento);

        return redirect()->route('admin.adultos-mayores.documentos.index', $adulto_mayor->cod_residente)->with('success', 'Documento archivado correctamente.');
    }

    public function restore(Residente $adulto_mayor, $id)
    {
        app(GestionarDocumentosResidenteHttp::class)->restore(auth()->user(), [], $adulto_mayor, $id);

        return redirect()->route('admin.adultos-mayores.documentos.index', $adulto_mayor->cod_residente)->with('success', 'Documento restaurado correctamente.');
    }
}
