<?php

namespace App\Backend\Modulos\Clinica\Acciones;

use App\Backend\Modulos\Identidad\Servicios\RolePreviewService;
use App\Backend\Modulos\Clinica\Servicios\ValidacionSignosVitalesService;
use App\Models\ObjetivoSignoVital;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

final class DefinirObjetivoSignoVitalAction
{
    /** @param array<string, mixed> $entrada */
    public function ejecutar(string $codResidente, array $entrada, User $medico): ObjetivoSignoVital
    {
        $this->autorizarMedico($medico);

        $datos = Validator::make($entrada, [
            'parametro' => ['required', Rule::in(array_keys(ObjetivoSignoVital::PARAMETROS))],
            'min_objetivo' => ['nullable', 'numeric', 'between:0,999999.99', 'decimal:0,2'],
            'max_objetivo' => ['nullable', 'numeric', 'between:0,999999.99', 'decimal:0,2'],
            'min_critico' => ['nullable', 'numeric', 'between:0,999999.99', 'decimal:0,2'],
            'max_critico' => ['nullable', 'numeric', 'between:0,999999.99', 'decimal:0,2'],
            'motivo' => ['required', 'string', 'min:10', 'max:1000'],
        ])->after(function ($validator) use ($entrada): void {
            $limitesTecnicos = [
                'presion_sistolica' => [1, ValidacionSignosVitalesService::PAS_MAX],
                'presion_diastolica' => [1, ValidacionSignosVitalesService::PAD_MAX],
                'frecuencia_cardiaca' => [1, ValidacionSignosVitalesService::FC_MAX],
                'frecuencia_respiratoria' => [1, ValidacionSignosVitalesService::FR_MAX],
                'temperatura' => [ValidacionSignosVitalesService::TEMP_MIN, ValidacionSignosVitalesService::TEMP_MAX],
                'saturacion_oxigeno' => [1, 100],
                'glucemia' => [1, 999999.99],
            ];
            if (isset($limitesTecnicos[$entrada['parametro'] ?? ''])) {
                [$minimo, $maximo] = $limitesTecnicos[$entrada['parametro']];
                foreach (['min_objetivo', 'max_objetivo', 'min_critico', 'max_critico'] as $campo) {
                    if (($entrada[$campo] ?? null) !== null && is_numeric($entrada[$campo])
                        && ((float) $entrada[$campo] < $minimo || (float) $entrada[$campo] > $maximo)) {
                        $validator->errors()->add($campo, 'El límite está fuera de la capacidad de esta medición.');
                    }
                }
            }
            if (($entrada['min_objetivo'] ?? null) === null && ($entrada['max_objetivo'] ?? null) === null) {
                $validator->errors()->add('min_objetivo', 'Indica al menos un límite del objetivo.');
            }
            foreach ([['min_objetivo', 'max_objetivo'], ['min_critico', 'min_objetivo'],
                ['max_objetivo', 'max_critico'], ['min_critico', 'max_objetivo'],
                ['min_objetivo', 'max_critico']] as [$a, $b]) {
                if (($entrada[$a] ?? null) !== null && ($entrada[$b] ?? null) !== null
                    && (float) $entrada[$a] >= (float) $entrada[$b]) {
                    $validator->errors()->add($b, 'Los límites deben mantener un orden creciente sin superponerse.');
                }
            }
            if (($entrada['min_critico'] ?? null) !== null && ($entrada['max_critico'] ?? null) !== null
                && (float) $entrada['min_critico'] >= (float) $entrada['max_critico']) {
                $validator->errors()->add('max_critico', 'El máximo crítico debe superar al mínimo crítico.');
            }
        })->validate();

        return DB::transaction(function () use ($codResidente, $datos, $medico): ObjetivoSignoVital {
            $residente = Residente::query()->whereKey($codResidente)->lockForUpdate()->firstOrFail();
            Gate::forUser($medico)->authorize('view', $residente);
            $codPersonal = Personal::query()->where('cod_usuario', $medico->cod_usuario)
                ->where('estado', 'ACTIVO')->value('cod_personal');
            abort_unless($codPersonal, 403, 'Se requiere un registro de personal médico activo.');

            $anterior = ObjetivoSignoVital::query()->where('cod_residente', $codResidente)
                ->where('parametro', $datos['parametro'])->where('estado', 'VIGENTE')
                ->lockForUpdate()->first();
            $momento = now();
            if ($anterior) {
                $anterior->update(['estado' => 'REEMPLAZADO', 'vigente_hasta' => $momento]);
            }

            $objetivo = ObjetivoSignoVital::create([
                'cod_residente' => $codResidente,
                'cod_personal' => $codPersonal,
                'parametro' => $datos['parametro'],
                'min_objetivo' => $datos['min_objetivo'] ?? null,
                'max_objetivo' => $datos['max_objetivo'] ?? null,
                'min_critico' => $datos['min_critico'] ?? null,
                'max_critico' => $datos['max_critico'] ?? null,
                'vigente_desde' => $momento,
                'estado' => 'VIGENTE',
                'motivo' => trim($datos['motivo']),
            ]);
            activity('ObjetivosSignosVitales')->causedBy($medico)->performedOn($objetivo)
                ->event('definido')->withProperties([
                    'cod_residente' => $codResidente,
                    'parametro' => $datos['parametro'],
                    'objetivo_anterior' => $anterior?->cod_objetivo_signo,
                ])->log('Objetivo médico de signos vitales definido');

            return $objetivo;
        });
    }

    public function retirar(string $codObjetivo, string $motivo, User $medico): void
    {
        $this->autorizarMedico($medico);
        Validator::make(['motivo' => $motivo], [
            'motivo' => ['required', 'string', 'min:10', 'max:1000'],
        ])->validate();

        DB::transaction(function () use ($codObjetivo, $motivo, $medico): void {
            $referencia = ObjetivoSignoVital::query()->findOrFail($codObjetivo);
            $residente = Residente::query()->whereKey($referencia->cod_residente)->lockForUpdate()->firstOrFail();
            Gate::forUser($medico)->authorize('view', $residente);
            abort_unless(Personal::query()->where('cod_usuario', $medico->cod_usuario)
                ->where('estado', 'ACTIVO')->exists(), 403, 'Se requiere un registro de personal médico activo.');
            $objetivo = ObjetivoSignoVital::query()->lockForUpdate()->findOrFail($codObjetivo);
            abort_unless($objetivo->estado === 'VIGENTE', 409, 'Este objetivo ya no está vigente.');
            $objetivo->update(['estado' => 'ANULADO', 'vigente_hasta' => now()]);
            activity('ObjetivosSignosVitales')->causedBy($medico)->performedOn($objetivo)
                ->event('retirado')->withProperties(['motivo' => trim($motivo)])
                ->log('Objetivo médico de signos vitales retirado');
        });
    }

    private function autorizarMedico(User $medico): void
    {
        abort_unless(Auth::user()?->cod_usuario === $medico->cod_usuario
            && $medico->estado === 'ACTIVO'
            && $medico->hasRole('MEDICO GENERAL/GERIATRA')
            && $medico->can('objetivos_signos_vitales.gestionar')
            && ! app(RolePreviewService::class)->isActive($medico), 403);
    }
}
