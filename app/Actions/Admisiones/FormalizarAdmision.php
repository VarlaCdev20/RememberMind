<?php

namespace App\Actions\Admisiones;

use App\Models\Admision;
use App\Models\Cama;
use App\Models\Consentimiento;
use App\Models\Contacto;
use App\Models\HistorialEstadoResidente;
use App\Models\OcupacionCama;
use App\Models\Preadmision;
use App\Models\Residente;
use App\Models\ResidenteContacto;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class FormalizarAdmision
{
    public function ejecutar(Preadmision $solicitud, array $datos, User $usuario): Residente
    {
        return DB::transaction(function () use ($solicitud, $datos, $usuario): Residente {
            $solicitud = Preadmision::query()->lockForUpdate()->findOrFail($solicitud->getKey());

            if ($solicitud->estado !== 'APROBADA' || $solicitud->admision()->exists()) {
                throw ValidationException::withMessages([
                    'solicitud' => 'La preadmisión debe estar APROBADA y pendiente de admisión formal.',
                ]);
            }

            $cama = Cama::query()->lockForUpdate()->findOrFail($datos['cod_cama']);
            if ($cama->estado !== 'ACTIVA' || OcupacionCama::query()->where('cod_cama', $cama->cod_cama)->where('estado', 'ACTIVA')->exists()) {
                throw ValidationException::withMessages(['cod_cama' => 'La cama no está disponible.']);
            }

            $contacto = $this->resolverContacto($solicitud, $datos);

            $residente = Residente::crearDesdeAdmision([
                'cod_residente' => $this->codigo('RES'),
                'nombres' => $solicitud->nombres,
                'apellido_paterno' => $solicitud->apellido_paterno,
                'apellido_materno' => $solicitud->apellido_materno,
                'numero_documento' => $solicitud->numero_documento,
                'complemento_documento' => $datos['complemento_documento'] ?? null,
                'expedicion_documento' => $solicitud->expedicion_documento,
                'fecha_nacimiento' => $solicitud->fecha_nacimiento,
                'genero' => $solicitud->genero,
                'estado_civil' => $solicitud->estado_civil,
                'telefono' => $solicitud->telefono,
                'celular' => $datos['celular'] ?? null,
                'direccion' => $solicitud->direccion,
                'nivel_educativo' => $datos['nivel_educativo'] ?? null,
                'grupo_sanguineo' => $datos['grupo_sanguineo'] ?? null,
                'factor_rh' => $datos['factor_rh'] ?? null,
                'foto' => $datos['foto'] ?? null,
                'estado' => 'ADMITIDO',
                'observacion' => $datos['observacion'] ?? null,
            ]);

            $admision = Admision::query()->create([
                'cod_admision' => $this->codigo('ADM'),
                'cod_preadmision' => $solicitud->cod_preadmision,
                'cod_residente' => $residente->cod_residente,
                'cod_usuario_registro' => $usuario->cod_usuario,
                'fecha_hora_admision' => $datos['fecha_hora_admision'] ?? now(),
                'tipo_ingreso' => $solicitud->tipo_ingreso,
                'procedencia' => $solicitud->procedencia,
                'motivo_ingreso' => $solicitud->motivo_ingreso,
                'estado' => 'ACTIVA',
                'observacion' => $datos['observacion'] ?? null,
            ]);

            $vinculo = ResidenteContacto::query()->create([
                'cod_residente_contacto' => $this->codigo('RCO'),
                'cod_residente' => $residente->cod_residente,
                'cod_contacto' => $contacto->cod_contacto,
                'parentesco' => $datos['parentesco'] ?? 'RESPONSABLE',
                'responsable_principal' => true,
                'contacto_emergencia' => true,
                'autoriza_informacion' => (bool) ($datos['autoriza_informacion'] ?? true),
                'autoriza_salida' => (bool) ($datos['autoriza_salida'] ?? false),
                'estado' => 'ACTIVO',
                'observacion' => $datos['observacion_contacto'] ?? null,
            ]);

            OcupacionCama::query()->create([
                'cod_ocupacion' => $this->codigo('OCU'),
                'cod_residente' => $residente->cod_residente,
                'cod_cama' => $cama->cod_cama,
                'cod_admision' => $admision->cod_admision,
                'cod_usuario_registro' => $usuario->cod_usuario,
                'fecha_hora_asignacion' => $admision->fecha_hora_admision,
                'estado' => 'ACTIVA',
            ]);

            HistorialEstadoResidente::query()->create([
                'cod_historial_estado' => $this->codigo('HES'),
                'cod_residente' => $residente->cod_residente,
                'cod_usuario_registro' => $usuario->cod_usuario,
                'estado_anterior' => null,
                'estado_nuevo' => 'ADMITIDO',
                'fecha_hora' => $admision->fecha_hora_admision,
                'motivo' => 'Admisión institucional formalizada.',
            ]);

            $firmaResidente = (bool) ($datos['firma_residente'] ?? false);
            Consentimiento::query()->create([
                'cod_consentimiento' => $this->codigo('CON'),
                'cod_residente' => $residente->cod_residente,
                'cod_admision' => $admision->cod_admision,
                'cod_residente_contacto' => $firmaResidente ? null : $vinculo->cod_residente_contacto,
                'cod_documento' => $datos['cod_documento_consentimiento'] ?? null,
                'cod_usuario_registro' => $usuario->cod_usuario,
                'tipo_consentimiento' => $datos['tipo_consentimiento'] ?? 'ADMISION',
                'firma_residente' => $firmaResidente,
                'fecha_consentimiento' => $datos['fecha_consentimiento'] ?? $admision->fecha_hora_admision,
                'estado' => 'VIGENTE',
                'observacion' => $datos['observacion_consentimiento'] ?? null,
            ]);

            $solicitud->update(['estado' => 'ADMITIDA']);

            activity('Admisiones')->causedBy($usuario)->performedOn($admision)
                ->log('Admisión formalizada con residente, contacto, cama, historial y consentimiento.');

            return $residente->fresh(['admisiones', 'vinculosContacto', 'ocupacionActiva']);
        }, 3);
    }

    private function resolverContacto(Preadmision $solicitud, array $datos): Contacto
    {
        $codigo = $datos['cod_contacto'] ?? $solicitud->cod_contacto;
        if ($codigo) {
            return Contacto::query()->lockForUpdate()->findOrFail($codigo);
        }

        $contacto = $datos['contacto'] ?? null;
        if (! is_array($contacto) || empty($contacto['nombres']) || empty($contacto['apellido_paterno'])) {
            throw ValidationException::withMessages(['cod_contacto' => 'La admisión requiere un contacto responsable.']);
        }

        return Contacto::query()->create(array_merge($contacto, [
            'cod_contacto' => $this->codigo('CTO'),
            'estado' => $contacto['estado'] ?? 'ACTIVO',
        ]));
    }

    private function codigo(string $prefijo): string
    {
        return $prefijo.'_'.Str::upper(Str::random(12));
    }
}
