<?php

namespace App\Actions\Admisiones;

use App\Models\AdultoMayor;
use App\Models\AsignacionAdultoMayor;
use App\Models\Cama;
use App\Models\DocumentoAdultoMayor;
use App\Models\EstadoAdulto;
use App\Models\Familiar;
use App\Models\FichaMedicaAdulto;
use App\Models\HistorialEstadoAdulto;
use App\Models\Preadmision;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FormalizarAdmision
{
    public function ejecutar(Preadmision $solicitud, array $datos, User $usuario): AdultoMayor
    {
        return DB::transaction(function () use ($solicitud, $datos, $usuario) {
            $solicitud = Preadmision::query()
                ->with('documentos')
                ->lockForUpdate()
                ->findOrFail($solicitud->getKey());

            if ($solicitud->estado !== 'APROBADA' || $solicitud->cod_am_generado) {
                throw ValidationException::withMessages([
                    'solicitud' => 'La solicitud debe estar aprobada y pendiente de admisión.',
                ]);
            }

            $cama = Cama::query()->with('habitacion')->lockForUpdate()->findOrFail($datos['cama_id']);

            if (! $cama->habitacion || $cama->cod_habitacion !== ($datos['habitacion_id'] ?? null)) {
                throw ValidationException::withMessages([
                    'cama_id' => 'La cama seleccionada no pertenece a la habitación indicada.',
                ]);
            }

            if (in_array($cama->habitacion->estado, ['MANTENIMIENTO', 'BLOQUEADA'], true)) {
                throw ValidationException::withMessages([
                    'habitacion_id' => 'La habitación no admite ingresos mientras esté en mantenimiento o bloqueada.',
                ]);
            }

            $ocupada = $cama->estado !== 'DISPONIBLE'
                || AsignacionAdultoMayor::query()
                    ->where('cod_cama', $cama->cod_cama)
                    ->whereIn('estado', ['ACTIVO', 'ACTIVA'])
                    ->exists();

            if ($ocupada) {
                throw ValidationException::withMessages([
                    'cama_id' => 'La cama seleccionada ya no está disponible. Seleccione otra.',
                ]);
            }

            $ci = $this->texto($solicitud->ci, 20);
            if (AdultoMayor::query()->where('ci', $ci)->exists()) {
                throw ValidationException::withMessages([
                    'solicitud' => 'Ya existe una persona residente registrada con esta cédula de identidad.',
                ]);
            }

            $estadoAdmitido = EstadoAdulto::firstOrCreate(['estado' => 'ADMITIDO']);
            $nombreResponsable = $this->texto($solicitud->familiar_completo, 150);
            $celular = $this->telefono($solicitud->celular, 20);
            $celularFamiliar = $this->telefono($solicitud->familiar_celular, 20);

            $adulto = AdultoMayor::create([
                'nombres' => $this->texto($solicitud->nombres, 100),
                'ap_paterno' => $this->texto($solicitud->ap_paterno, 80),
                'ap_materno' => $this->texto($solicitud->ap_materno, 80),
                'ci' => $ci,
                'expedicion_ci' => $this->texto($solicitud->expedicion_ci, 50),
                'fecha_nac' => $solicitud->fecha_nac,
                'genero' => $this->texto($solicitud->genero, 100),
                'estado_civil' => $this->texto($solicitud->estado_civil, 100),
                'telefono' => $this->telefono($solicitud->telefono, 20),
                'tiene_celular' => $celular ? 'SI' : 'NO',
                'celular' => $celular,
                'sabe_usar_whatsapp' => ! empty($datos['usa_whatsapp']) ? 'SI' : 'NO',
                'departamento_residencia' => $this->texto($solicitud->departamento_residencia, 100),
                'ciudad_municipio' => $this->texto($solicitud->ciudad_municipio, 100),
                'zona' => $this->texto($solicitud->zona, 100),
                'calle' => $this->texto($solicitud->calle, 150),
                'fecha_ing' => $datos['fecha_ingreso'],
                'hora_ing' => $datos['hora_ingreso'],
                'tipo_ing' => $this->texto($solicitud->tipo_ingreso ?: 'REGULAR', 100),
                'permanencia' => $this->texto($solicitud->permanencia ?: 'PERMANENTE', 50),
                'nivel_educat' => $this->texto($datos['nivel_educativo'] ?? null, 100),
                'grupo_sanguineo' => $this->texto($datos['grupo_sanguineo'] ?? null, 10),
                'factor_rh' => $this->texto($datos['factor_rh'] ?? null, 5),
                'alergias' => $this->textoLargo($datos['alergias']),
                'seguro_salud' => $this->texto($datos['seguro_salud'], 100),
                'contacto_emergencia_nombre' => $nombreResponsable,
                'contacto_emergencia_parentesco' => $this->texto($solicitud->familiar_parentesco, 100),
                'contacto_emergencia_celular' => $celularFamiliar,
                'contacto_emergencia_direccion' => $this->texto($solicitud->familiar_direccion, 255),
                'responsable_principal' => $nombreResponsable,
                'autorizado_informacion_medica' => ! empty($datos['autoriza_informacion_medica']) ? 'SI' : 'NO',
                'consentimiento_datos' => (bool) $datos['consentimiento_datos'],
                'observaciones' => $this->textoLargo(implode(' | ', array_filter([
                    $solicitud->descripcion_caso,
                    $solicitud->direccion_referencia ? 'Referencia domiciliaria: '.$solicitud->direccion_referencia : null,
                    $datos['observaciones_admision'] ?? null,
                ]))),
                'cod_est_adul' => $estadoAdmitido->cod_est_adul,
                'motivo_ingreso' => $this->textoLargo($solicitud->motivo_ingreso),
                'procedencia_ingreso' => $this->texto($solicitud->procedencia_ingreso, 150),
                'cod_pre_origen' => $solicitud->cod_pre,
            ]);

            $antecedentes = collect($datos['antecedentes'] ?? [])->map(fn ($valor) => strtoupper((string) $valor));
            FichaMedicaAdulto::create([
                'cod_am' => $adulto->cod_am,
                'hipertension' => $antecedentes->contains('HIPERTENSION'),
                'diabetes' => $antecedentes->contains('DIABETES'),
                'problemas_cardiacos' => $antecedentes->contains('PROBLEMAS_CARDIACOS'),
                'acv' => $antecedentes->contains('ACV'),
                'parkinson' => $antecedentes->contains('PARKINSON'),
                'epilepsia' => $antecedentes->contains('EPILEPSIA'),
                'alzheimer_diagnosticado' => $antecedentes->contains('ALZHEIMER'),
                'depresion' => $antecedentes->contains('DEPRESION'),
                'ansiedad' => $antecedentes->contains('ANSIEDAD'),
                'problemas_sueno' => $antecedentes->contains('PROBLEMAS_SUENO'),
                'problemas_visuales' => $antecedentes->contains('PROBLEMAS_VISUALES'),
                'problemas_auditivos' => $antecedentes->contains('PROBLEMAS_AUDITIVOS'),
                'dolor_cronico' => $antecedentes->contains('DOLOR_CRONICO'),
                'alergias' => $this->textoLargo($datos['alergias']),
                'restricciones_alimentarias' => $this->textoLargo($datos['restricciones_alimentarias'] ?? null),
                'hospitalizaciones' => $this->textoLargo($datos['hospitalizaciones'] ?? null),
                'cirugias' => $this->textoLargo($datos['cirugias'] ?? null),
                'observacion_medica' => $this->textoLargo($datos['observacion_medica'] ?? null),
                'registrado_por' => $usuario->cod_usu,
                'estado' => 'ACTIVO',
            ]);

            $ciFamiliar = $this->texto($solicitud->familiar_ci, 20);
            $familiar = $ciFamiliar
                ? Familiar::firstOrNew(['ci' => $ciFamiliar])
                : new Familiar;
            $familiar->fill([
                'nombres' => $this->texto($solicitud->familiar_nombres, 100),
                'ap_paterno' => $this->texto($solicitud->familiar_ap_paterno, 80),
                'ap_materno' => $this->texto($solicitud->familiar_ap_materno, 80),
                'ci' => $ciFamiliar,
                'parentesco_vinculo' => $this->texto($solicitud->familiar_parentesco, 100),
                'celular' => $celularFamiliar,
                'correo' => $this->texto($solicitud->familiar_correo, 120),
                'direccion' => $this->texto($solicitud->familiar_direccion, 255),
                'es_responsable' => true,
                'estado' => 'ACTIVO',
                'observaciones' => 'Responsable registrado durante la admisión institucional.',
            ]);
            $familiar->save();

            $adulto->familiares()->syncWithoutDetaching([
                $familiar->cod_fam => [
                    'parentesco_vinculo' => $this->texto($solicitud->familiar_parentesco, 80),
                    'es_responsable' => true,
                    'estado' => 'ACTIVO',
                    'observaciones' => 'Vínculo confirmado durante la admisión institucional.',
                ],
            ]);

            $documentoRespaldo = null;
            foreach ($solicitud->documentos as $documento) {
                $migrado = DocumentoAdultoMayor::create([
                    'cod_am' => $adulto->cod_am,
                    'nombre' => $this->texto($documento->nombre_documento, 180) ?: 'Documento de preadmisión',
                    'tipo_documento' => $this->texto($documento->tipo_documento, 80) ?: 'PREADMISION',
                    'ruta_archivo' => $documento->archivo_path ?: 'PENDIENTE_PREADMISION',
                    'fecha_subida' => $documento->created_at?->toDateString() ?: now()->toDateString(),
                    'estado' => in_array($documento->estado, ['PENDIENTE', 'PENDIENTE_48H'], true) ? 'PENDIENTE' : 'ACTIVO',
                    'observaciones' => $documento->observaciones,
                    'modulo_ref' => 'PREADMISION',
                ]);
                $documentoRespaldo ??= $migrado;
            }

            AsignacionAdultoMayor::create([
                'cod_am' => $adulto->cod_am,
                'cod_habitacion' => $cama->cod_habitacion,
                'cod_cama' => $cama->cod_cama,
                'fecha_asignacion' => $datos['fecha_ingreso'],
                'hora_asignacion' => $datos['hora_ingreso'],
                'estado' => 'ACTIVO',
                'observaciones' => 'Primera ocupación registrada durante la admisión institucional.',
                'registrado_por' => $usuario->cod_usu,
            ]);

            HistorialEstadoAdulto::create([
                'cod_am' => $adulto->cod_am,
                'estado_anterior' => null,
                'estado_nuevo' => $estadoAdmitido->cod_est_adul,
                'fecha_cambio' => now(),
                'motivo' => 'Admisión institucional formalizada.',
                'documento_respaldo' => $documentoRespaldo?->cod_doc_am,
                'cambiado_por' => $usuario->cod_usu,
                'observacion' => 'Ingreso con cama y responsable principal confirmados.',
            ]);

            $solicitud->update([
                'estado' => 'ADMITIDA',
                'cod_am_generado' => $adulto->cod_am,
                'cod_fam_generado' => $familiar->cod_fam,
            ]);

            activity('Admisiones')
                ->causedBy($usuario)
                ->performedOn($solicitud)
                ->log('Admisión institucional formalizada y residente habilitado.');

            return $adulto->fresh(['estado', 'habitacion', 'cama', 'familiares']);
        });
    }

    private function texto(mixed $valor, int $max): ?string
    {
        $texto = trim((string) $valor);

        return $texto === '' ? null : mb_substr($texto, 0, $max, 'UTF-8');
    }

    private function textoLargo(mixed $valor): ?string
    {
        $texto = trim((string) $valor);

        return $texto === '' ? null : $texto;
    }

    private function telefono(mixed $valor, int $max): ?string
    {
        $numero = preg_replace('/[^0-9+]/', '', (string) $valor);

        return $numero === '' ? null : mb_substr($numero, 0, $max);
    }
}
