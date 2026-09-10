<?php

namespace App\Http\Controllers\Documentos;

use App\Http\Controllers\Controller;
use App\Http\Requests\Documentos\StoreDocumentoAdultoRequest;
use App\Models\AdultoMayor;
use App\Models\DocumentoAdultoMayor;
use Illuminate\Http\Request;

class AdultoMayorDocumentoController extends Controller
{
    public function index(AdultoMayor $adulto_mayor)
    {
        $adulto_mayor->load(['documentos' => function($q) {
            $q->latest();
        }]);
        return view('pages.adultos-mayores.documentos.index', compact('adulto_mayor'));
    }

    public function store(StoreDocumentoAdultoRequest $request, AdultoMayor $adulto_mayor)
    {
        $data = $request->validated();

        if ($request->hasFile('archivo')) {
            $file = $request->file('archivo');
            $path = $file->store('documentos/adultos-mayores', 'public');
            $data['ruta_archivo'] = $path;
            $data['estado'] = 'ACTIVO';
        }

        $adulto_mayor->documentos()->create($data);

        activity('Adulto Mayor')
            ->performedOn($adulto_mayor)
            ->log("Se subió un documento para el adulto mayor: {$adulto_mayor->nombres}");

        return redirect()->route('admin.adultos-mayores.documentos.index', $adulto_mayor->cod_am)->with('success', 'Documento subido correctamente.');
    }

    public function update(Request $request, AdultoMayor $adulto_mayor, $documento)
    {
        $doc = $adulto_mayor->documentos()->findOrFail($documento);
        
        $request->validate([
            'nombre' => 'required|string|max:255',
            'tipo_documento' => 'required|string',
            'observaciones' => 'nullable|string',
        ]);

        $doc->update($request->only(['nombre', 'tipo_documento', 'observaciones']));

        return redirect()->route('admin.adultos-mayores.documentos.index', $adulto_mayor->cod_am)->with('success', 'Metadatos del documento actualizados.');
    }

    public function destroy(AdultoMayor $adulto_mayor, $documento)
    {
        $doc = $adulto_mayor->documentos()->findOrFail($documento);
        
        $doc->delete();

        activity('Adulto Mayor')
            ->performedOn($adulto_mayor)
            ->log("Se archivó un documento (baja lógica) de la ficha {$adulto_mayor->cod_am}. Se conserva archivo físico.");

        return redirect()->route('admin.adultos-mayores.documentos.index', $adulto_mayor->cod_am)->with('success', 'Documento archivado correctamente.');
    }

    public function restore(AdultoMayor $adulto_mayor, $id)
    {
        $doc = DocumentoAdultoMayor::findOrFail($id);
        $doc->update(['estado' => 'ACTIVO']);

        activity('Adulto Mayor')
            ->performedOn($adulto_mayor)
            ->withProperties(['cod_doc' => $id])
            ->log("Se restauró un documento previamente archivado.");

        return redirect()->route('admin.adultos-mayores.documentos.index', $adulto_mayor->cod_am)
            ->with('success', 'Documento restaurado correctamente.');
    }
}
