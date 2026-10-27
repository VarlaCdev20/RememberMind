<?php

namespace App\Modulos\Identidad\Aplicacion\Acciones;

use App\Models\DestinatarioSolicitudDocumental;
use App\Models\Documento;
use App\Models\EntregaSolicitudDocumental;
use App\Models\User;
use App\Modulos\Identidad\Aplicacion\Consultas\CapacidadDocumentalPersonal;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

final class RegistrarArchivoSolicitudDocumental
{
    public function ejecutar(User $actor, string $destinatario, UploadedFile $archivo, ?string $fechaEmision = null): EntregaSolicitudDocumental
    {
        app(CapacidadDocumentalPersonal::class)->exigir();
        Validator::make(compact('archivo', 'fechaEmision'), ['archivo' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
            'fechaEmision' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:today']])->validate();
        $ruta = null;
        try {
            return DB::transaction(function () use ($actor, $destinatario, $archivo, $fechaEmision, &$ruta) {
                $destino = DestinatarioSolicitudDocumental::with('personal.usuario', 'solicitud')->lockForUpdate()->findOrFail($destinatario);
                $actor = app(GestionarSolicitudesDocumentales::class)->autorizarEntrega($actor, $destino);
                $titular = User::query()->lockForUpdate()->findOrFail($destino->personal->cod_usuario);
                $hash = hash_file('sha256', $archivo->getRealPath());
                $existente = $destino->entregas()->with('documento')->whereHas('documento', fn ($q) => $q->where('hash_archivo', $hash)->whereNotIn('estado', ['ANULADO', 'ANULADA', 'REEMPLAZADO']))->first();
                if ($existente && $existente->documento->fecha_emision?->toDateString() === $fechaEmision) {
                    return $existente;
                }
                $anterior = $destino->entregas()->with('documento')->orderByDesc('fecha_hora_registro')->orderByDesc('cod_entrega_documental')->get()
                    ->pluck('documento')->first(fn ($doc) => $doc && ! in_array($doc->estado, ['ANULADO', 'ANULADA', 'REEMPLAZADO'], true));
                $ruta = $archivo->storeAs('documentos_personal_privados/'.$titular->getKey(), Str::uuid().'.'.$archivo->guessExtension(), 'local');
                if (! $ruta) {
                    throw new \RuntimeException('No se pudo conservar el archivo.');
                }
                $documento = Documento::create(['cod_usuario' => $titular->getKey(), 'cod_documento_anterior' => $anterior?->getKey(),
                    'tipo_documento' => $destino->solicitud->tipo_documento, 'nombre' => $destino->solicitud->nombre_documento,
                    'ruta_archivo' => $ruta, 'tipo_archivo' => $archivo->getMimeType(), 'hash_archivo' => $hash, 'estado' => 'CARGADO',
                    'cod_usuario_carga' => $actor->getKey(), 'fecha_hora_carga' => now(), 'fecha_emision' => $fechaEmision]);
                $anterior?->update(['estado' => 'REEMPLAZADO']);
                $entrega = app(GestionarSolicitudesDocumentales::class)->vincularEntrega($actor, $destino->getKey(), $documento->getKey());
                activity('Personal')->causedBy($actor)->performedOn($documento)->event('entrega_documental')->log('Registró una entrega documental; requiere revisión.');

                return $entrega;
            });
        } catch (\Throwable $error) {
            if ($ruta && ! Storage::disk('local')->delete($ruta)) {
                report(new \RuntimeException('No se pudo compensar un archivo documental nuevo.'));
            }
            throw $error;
        }
    }
}
