<?php

namespace App\Modulos\Residentes\Aplicacion\Acciones;

use App\Compartido\Aplicacion\Contratos\ArchivosAplicacion;
use App\Models\Documento;
use App\Models\Residente;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

final class RegistrarDocumentoHttp
{
    public function __construct(private readonly ArchivosAplicacion $ficheros) {}

    public function store(User $usuario, array $entrada, Residente $residente): mixed
    {
        Gate::forUser($usuario)->authorize('view', $residente);
        abort_unless($usuario->can('documentos.gestionar'), 403);
        $datos = Validator::make($entrada, ['tipo_documento' => ['required', 'string', 'max:60'], 'nombre' => ['required', 'string', 'max:160'], 'archivo' => ['required', 'file', 'max:20480'], 'fecha_vencimiento' => ['nullable', 'date'], 'observacion' => ['nullable', 'string'], 'cod_documento_anterior' => ['nullable', 'exists:documentos,cod_documento']])->validate();
        $archivo = data_get($entrada, 'archivo');
        $ruta = $this->ficheros->recibir($archivo, 'documentos', 'local');
        $documento = Documento::query()->create(['cod_documento' => $this->codigo('DOC'), 'cod_residente' => $residente->cod_residente, 'tipo_documento' => $datos['tipo_documento'], 'nombre' => $datos['nombre'], 'ruta_archivo' => $ruta, 'tipo_archivo' => $archivo->getMimeType() ?: 'application/octet-stream', 'hash_archivo' => $this->ficheros->hashEntrada($archivo), 'fecha_vencimiento' => $datos['fecha_vencimiento'] ?? null, 'cod_documento_anterior' => $datos['cod_documento_anterior'] ?? null, 'estado' => 'PENDIENTE', 'observacion' => $datos['observacion'] ?? null]);

        return $documento;
    }

    private function codigo(string $prefijo): string
    {
        return $prefijo.'_'.Str::upper(Str::random(12));
    }
}
