<?php

namespace App\Modulos\Cuidados\Aplicacion\Acciones;

use App\Models\Atencion;
use App\Models\RegistroConductual;
use App\Models\Residente;
use App\Models\User;
use App\Modulos\Clinica\Aplicacion\Acciones\AutorizacionClinicaService;
use App\Modulos\Cuidados\Aplicacion\Consultas\MiTurnoService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class RegistrarConducta
{
    public const INDICADORES = ['apatia', 'agitacion', 'agresividad', 'ansiedad', 'aislamiento', 'deambulacion', 'cambio_conducta'];

    public function ejecutar(User $usuario, Residente $residente, array $entrada): RegistroConductual
    {
        $personal = app(AutorizacionClinicaService::class)->autorizarMutacion($usuario, $residente, 'registros_conductuales.crear', ['ENFERMEROS', 'PSICOLOGO/A']);
        foreach (self::INDICADORES as $campo) {
            if (($entrada[$campo] ?? null) === '') {
                $entrada[$campo] = null;
            }
        }
        foreach (['estado_animo', 'participacion', 'descripcion', 'intervencion', 'respuesta'] as $campo) {
            if (isset($entrada[$campo]) && is_string($entrada[$campo])) {
                $entrada[$campo] = trim($entrada[$campo]);
                if ($entrada[$campo] === '') {
                    $entrada[$campo] = null;
                }
            }
        }
        $reglas = ['estado_animo' => 'nullable|string|max:40', 'participacion' => 'nullable|string|max:30',
            'descripcion' => 'nullable|string', 'intervencion' => 'nullable|string', 'respuesta' => 'nullable|string',
            'cod_atencion' => 'nullable|string|max:20|exists:atenciones,cod_atencion'];
        foreach (self::INDICADORES as $campo) {
            $reglas[$campo] = 'nullable|boolean';
        }
        $datos = Validator::make($entrada, $reglas)->validate();
        foreach (['estado_animo', 'participacion', 'descripcion', 'intervencion', 'respuesta'] as $campo) {
            $texto = isset($datos[$campo]) ? trim($datos[$campo]) : '';
            $datos[$campo] = $texto === '' ? null : $texto;
        }
        foreach (self::INDICADORES as $campo) {
            $datos[$campo] = isset($datos[$campo]) ? (bool) $datos[$campo] : null;
        }
        $jornada = app(MiTurnoService::class)->resolverJornadaActual($personal, now());
        if ($usuario->hasRole('ENFERMEROS') && ! $jornada) {
            throw ValidationException::withMessages(['jornada' => 'Se requiere una jornada de Enfermería activa para registrar conducta.']);
        }

        return DB::transaction(function () use ($usuario, $residente, $datos, $personal, $jornada): RegistroConductual {
            if (filled($datos['cod_atencion'] ?? null)) {
                $atencion = Atencion::where('cod_residente', $residente->getKey())->find($datos['cod_atencion']);
                if (! $atencion) {
                    throw ValidationException::withMessages(['cod_atencion' => 'La atención debe pertenecer al residente actual.']);
                }
            }
            $registro = RegistroConductual::create([
                ...$datos, 'cod_registro_conductual' => app(\App\Compartido\Aplicacion\Contratos\CodigosEntidad::class)->siguiente('registros_conductuales'),
                'cod_residente' => $residente->getKey(), 'cod_personal' => $personal->getKey(),
                'cod_jornada' => $jornada?->getKey(), 'fecha_hora' => now(), 'estado' => 'VIGENTE',
            ]);
            activity('conducta')->causedBy($usuario)->performedOn($registro)->log('Registro de conducta confirmado');

            return $registro;
        });
    }
}
