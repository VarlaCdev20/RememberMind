<?php

namespace App\Modulos\Residentes\Aplicacion\Acciones;

use App\Models\Residente;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

final class GestionarDocumentosResidenteHttp
{
    public function store(User $actor, array $entrada, Residente $adulto_mayor): mixed
    {
        abort_unless($actor->can('documentos.gestionar'), 403);
        $this->autorizarResidente($actor, $adulto_mayor);
        $data = $entrada;
        $file = data_get($entrada, 'archivo');
        $path = $file->store('documentos/residentes', 'local');
        $adulto_mayor->documentos()->create(['cod_documento' => 'DOC_'.Str::upper(Str::random(10)), 'cod_residente' => $adulto_mayor->cod_residente, 'tipo_documento' => $data['tipo_documento'], 'nombre' => $data['nombre'], 'ruta_archivo' => $path, 'tipo_archivo' => $file->getMimeType() ?: 'application/octet-stream', 'hash_archivo' => hash_file('sha256', Storage::disk('local')->path($path)), 'estado' => 'ACTIVO', 'observacion' => $data['observaciones'] ?? null]);
        activity('Adulto Mayor')->causedBy($actor)->performedOn($adulto_mayor)->log("Se subió un documento para el adulto mayor: {$adulto_mayor->nombres}");

        return null;
    }

    public function update(User $actor, array $entrada, Residente $adulto_mayor, $documento): mixed
    {
        abort_unless($actor->can('documentos.gestionar'), 403);
        $this->autorizarResidente($actor, $adulto_mayor);
        $doc = $adulto_mayor->documentos()->findOrFail($documento);
        Validator::make($entrada, ['nombre' => 'required|string|max:255', 'tipo_documento' => 'required|string', 'observaciones' => 'nullable|string|max:1000'])->validate();
        $doc->update(['nombre' => str(data_get($entrada, 'nombre'))->toString(), 'tipo_documento' => str(data_get($entrada, 'tipo_documento'))->toString(), 'observacion' => data_get($entrada, 'observaciones')]);

        return null;
    }

    public function destroy(User $actor, array $entrada, Residente $adulto_mayor, $documento): mixed
    {
        abort_unless($actor->can('documentos.gestionar'), 403);
        $this->autorizarResidente($actor, $adulto_mayor);
        $doc = $adulto_mayor->documentos()->findOrFail($documento);
        $doc->update(['estado' => 'ARCHIVADO']);
        activity('Adulto Mayor')->causedBy($actor)->performedOn($adulto_mayor)->log("Se archivó un documento (baja lógica) de la ficha {$adulto_mayor->cod_residente}. Se conserva archivo físico.");

        return null;
    }

    public function restore(User $actor, array $entrada, Residente $adulto_mayor, $id): mixed
    {
        abort_unless($actor->can('documentos.gestionar'), 403);
        $this->autorizarResidente($actor, $adulto_mayor);
        $doc = $adulto_mayor->documentos()->findOrFail($id);
        $doc->update(['estado' => 'ACTIVO']);
        activity('Adulto Mayor')->causedBy($actor)->performedOn($adulto_mayor)->withProperties(['cod_doc' => $id])->log('Se restauró un documento previamente archivado.');

        return null;
    }

    private function autorizarResidente(User $actor, Residente $residente): void
    {
        abort_unless($actor->can('residentes.ver'), 403);
        Gate::forUser($actor)->authorize('view', $residente);
    }
}
