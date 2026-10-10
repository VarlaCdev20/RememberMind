<?php

namespace App\Modulos\Residentes\Aplicacion\Acciones;

use App\Models\Documento;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

final class IncorporarDocumentosExpediente
{
    public function guardarNuevoDocumento(User $usuario, array $entrada): mixed
    {

        abort_unless(
            $usuario?->can('documentos.subir') || $usuario?->can('documentos.gestionar'),
            403,
            'No cuenta con permiso para incorporar documentos al expediente.',
        );

        Validator::make($entrada, [
            'nuevoDocNombre' => 'required|string|min:3|max:255',
            'nuevoDocTipo' => 'required|string|max:50',
            'nuevoDocCategoria' => 'required|in:CLINICO,ADMINISTRATIVO,IMAGEN,LEGAL,PERSONAL',
            'nuevoDocFecha' => 'required|date|before_or_equal:today',
            'nuevoDocDescripcion' => 'nullable|string|max:1000',
            'nuevoDocObservaciones' => 'nullable|string|max:1000',
            'nuevoDocArchivo' => 'required|file|mimes:pdf,jpg,jpeg,png,webp|max:15360', // 15MB
        ], [
            'nuevoDocNombre.required' => 'El nombre del documento es obligatorio.',
            'nuevoDocArchivo.required' => 'Seleccione el archivo real que será incorporado al expediente.',
            'nuevoDocArchivo.mimes' => 'Solo se admiten archivos en formato PDF, JPG, PNG o WebP.',
            'nuevoDocArchivo.max' => 'El tamaño máximo del archivo no debe exceder los 15 MB.',
        ])->validate();

        $hashArchivo = hash_file('sha256', $entrada['nuevoDocArchivo']->getRealPath());
        $rutaArchivo = $entrada['nuevoDocArchivo']->store('documentos/adultos-mayores', 'local');

        $doc = Documento::create([
            'cod_documento' => 'DOC_'.Str::upper(Str::random(12)),
            'cod_residente' => $entrada['adultoMayor']->cod_residente,
            'cod_usuario' => $usuario->getKey(),
            'nombre' => trim($entrada['nuevoDocNombre']),
            'tipo_documento' => $entrada['nuevoDocTipo'],
            'ruta_archivo' => $rutaArchivo,
            'tipo_archivo' => $entrada['nuevoDocArchivo']->getMimeType(),
            'hash_archivo' => $hashArchivo,
            'fecha_validacion' => ($entrada['nuevoDocFecha'] ?: today()->toDateString()).' 00:00:00',
            'estado' => 'ACTIVO',
            'observacion' => $entrada['nuevoDocObservaciones'] ?: $entrada['nuevoDocDescripcion'],
        ]);

        activity('Documentos')
            ->performedOn($entrada['adultoMayor'])
            ->log("Se subió el documento {$doc->nombre} ({$doc->cod_documento}) para el residente {$entrada['adultoMayor']->nombres}");

        return $doc;
    }
}
