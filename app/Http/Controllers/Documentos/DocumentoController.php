<?php

namespace App\Http\Controllers\Documentos;

use App\Http\Controllers\Controller;
use App\Models\Documento;
use App\Models\Residente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentoController extends Controller
{
    public function store(Request $request, Residente $residente): JsonResponse
    {
        $this->authorize('view', $residente);
        abort_unless($request->user()->can('documentos.gestionar'), 403);
        $datos = $request->validate(['tipo_documento' => ['required', 'string', 'max:60'], 'nombre' => ['required', 'string', 'max:160'], 'archivo' => ['required', 'file', 'max:20480'], 'fecha_vencimiento' => ['nullable', 'date'], 'observacion' => ['nullable', 'string'], 'cod_documento_anterior' => ['nullable', 'exists:documentos,cod_documento']]);
        $archivo = $request->file('archivo');
        $ruta = $archivo->store('documentos', 'local');
        $documento = Documento::query()->create(['cod_documento' => $this->codigo('DOC'), 'cod_residente' => $residente->cod_residente, 'tipo_documento' => $datos['tipo_documento'], 'nombre' => $datos['nombre'], 'ruta_archivo' => $ruta, 'tipo_archivo' => $archivo->getMimeType() ?: 'application/octet-stream', 'hash_archivo' => hash_file('sha256', $archivo->getRealPath()), 'fecha_vencimiento' => $datos['fecha_vencimiento'] ?? null, 'cod_documento_anterior' => $datos['cod_documento_anterior'] ?? null, 'estado' => 'PENDIENTE', 'observacion' => $datos['observacion'] ?? null]);
        return response()->json($documento, 201);
    }

    public function descargar(Request $request, Documento $documento): StreamedResponse
    {
        abort_unless($request->user()->can('documentos.ver'), 403);
        if ($documento->cod_residente) {
            $this->authorize('view', Residente::query()->findOrFail($documento->cod_residente));
        }
        if ($request->user()->hasRole('FAMILIAR') && ! $documento->cod_residente) {
            $contactos = $request->user()->contactos()->pluck('cod_contacto');
            abort_unless(
                $documento->cod_usuario === $request->user()->cod_usuario
                || ($documento->cod_contacto && $contactos->contains($documento->cod_contacto)),
                403
            );
        }
        abort_unless(Storage::disk('local')->exists($documento->ruta_archivo), 404);
        activity('Documentos')->causedBy($request->user())->performedOn($documento)->log('Documento administrativo descargado.');
        return Storage::disk('local')->download($documento->ruta_archivo, $documento->nombre);
    }

    private function codigo(string $prefijo): string { return $prefijo.'_'.Str::upper(Str::random(12)); }
}
