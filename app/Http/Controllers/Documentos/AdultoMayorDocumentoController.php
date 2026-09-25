<?php

namespace App\Http\Controllers\Documentos;

use App\Http\Controllers\Controller;
use App\Http\Requests\Documentos\StoreDocumentoAdultoRequest;
use App\Models\AdultoMayor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdultoMayorDocumentoController extends Controller
{
    public function index(AdultoMayor $adulto_mayor)
    {
        $adulto_mayor->load(['documentos' => function($q) {
            $q->orderByDesc('cod_documento');
        }]);
        return view('pages.adultos-mayores.documentos.index', compact('adulto_mayor'));
    }

    public function store(StoreDocumentoAdultoRequest $request, AdultoMayor $adulto_mayor)
    {
        $data = $request->validated();
        $file = $request->file('archivo');
        $path = $file->store('documentos/residentes', 'local');

        $adulto_mayor->documentos()->create([
            'cod_documento' => 'DOC_' . Str::upper(Str::random(10)),
            'cod_residente' => $adulto_mayor->cod_residente,
            'tipo_documento' => $data['tipo_documento'],
            'nombre' => $data['nombre'],
            'ruta_archivo' => $path,
            'tipo_archivo' => $file->getMimeType() ?: 'application/octet-stream',
            'hash_archivo' => hash_file('sha256', Storage::disk('local')->path($path)),
            'estado' => 'ACTIVO',
            'observacion' => $data['observaciones'] ?? null,
        ]);

        activity('Adulto Mayor')
            ->performedOn($adulto_mayor)
            ->log("Se subió un documento para el adulto mayor: {$adulto_mayor->nombres}");

        return redirect()->route('admin.adultos-mayores.documentos.index', $adulto_mayor->cod_residente)->with('success', 'Documento subido correctamente.');
    }

    public function archivo(Request $request, AdultoMayor $adulto_mayor, string $documento)
    {
        $doc = $adulto_mayor->documentos()->findOrFail($documento);
        $disco = \Illuminate\Support\Facades\Storage::disk('local');
        if (!$disco->exists($doc->ruta_archivo)) $disco = \Illuminate\Support\Facades\Storage::disk('public');
        abort_unless($disco->exists($doc->ruta_archivo), 404, 'Archivo no disponible.');

        if ($request->has('ver') || $request->has('preview') || $request->has('inline')) {
            $path = $disco->path($doc->ruta_archivo);
            $mime = $disco->mimeType($doc->ruta_archivo) ?: 'application/pdf';
            return response()->file($path, ['Content-Type' => $mime]);
        }

        return $disco->download($doc->ruta_archivo);
    }

    public function update(Request $request, AdultoMayor $adulto_mayor, $documento)
    {
        $doc = $adulto_mayor->documentos()->findOrFail($documento);
        
        $request->validate([
            'nombre' => 'required|string|max:255',
            'tipo_documento' => 'required|string',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        $doc->update([
            'nombre' => $request->string('nombre')->toString(),
            'tipo_documento' => $request->string('tipo_documento')->toString(),
            'observacion' => $request->input('observaciones'),
        ]);

        return redirect()->route('admin.adultos-mayores.documentos.index', $adulto_mayor->cod_residente)->with('success', 'Metadatos del documento actualizados.');
    }

    public function destroy(AdultoMayor $adulto_mayor, $documento)
    {
        $doc = $adulto_mayor->documentos()->findOrFail($documento);
        
        $doc->update(['estado' => 'ARCHIVADO']);

        activity('Adulto Mayor')
            ->performedOn($adulto_mayor)
            ->log("Se archivó un documento (baja lógica) de la ficha {$adulto_mayor->cod_residente}. Se conserva archivo físico.");

        return redirect()->route('admin.adultos-mayores.documentos.index', $adulto_mayor->cod_residente)->with('success', 'Documento archivado correctamente.');
    }

    public function restore(AdultoMayor $adulto_mayor, $id)
    {
        $doc = $adulto_mayor->documentos()->findOrFail($id);
        $doc->update(['estado' => 'ACTIVO']);

        activity('Adulto Mayor')
            ->performedOn($adulto_mayor)
            ->withProperties(['cod_doc' => $id])
            ->log("Se restauró un documento previamente archivado.");

        return redirect()->route('admin.adultos-mayores.documentos.index', $adulto_mayor->cod_residente)
            ->with('success', 'Documento restaurado correctamente.');
    }
}
