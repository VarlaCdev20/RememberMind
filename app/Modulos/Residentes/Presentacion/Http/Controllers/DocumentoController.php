<?php

namespace App\Modulos\Residentes\Presentacion\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Documento;
use App\Models\Residente;
use App\Modulos\Residentes\Aplicacion\Acciones\RegistrarDocumentoHttp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentoController extends Controller
{
    public function store(Request $request, Residente $residente): JsonResponse
    {
        return response()->json(app(RegistrarDocumentoHttp::class)->store($request->user(), $request->all(), $residente), 201);
    }

    public function descargar(Request $request, Documento $documento): StreamedResponse
    {
        abort_unless($request->user()->can('documentos.ver'), 403);
        if ($documento->cod_residente) {
            $this->authorize('view', Residente::query()->findOrFail($documento->cod_residente));
            abort_if($request->user()->hasRole('FAMILIAR'), 403, 'Este documento no está publicado para familiares.');
        }
        if ($request->user()->hasRole('FAMILIAR') && ! $documento->cod_residente) {
            $contactos = $request->user()->contactos()->where('estado', 'ACTIVO')->pluck('cod_contacto');
            abort_unless($documento->cod_usuario === $request->user()->cod_usuario || $documento->cod_contacto && $contactos->contains($documento->cod_contacto), 403);
        }
        abort_unless(Storage::disk('local')->exists($documento->ruta_archivo), 404);
        activity('Documentos')->causedBy($request->user())->performedOn($documento)->log('Documento administrativo descargado.');

        return Storage::disk('local')->download($documento->ruta_archivo, $documento->nombre);
    }
}
