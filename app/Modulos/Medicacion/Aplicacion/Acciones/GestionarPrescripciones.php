<?php

namespace App\Modulos\Medicacion\Aplicacion\Acciones;

use App\Models\Atencion;
use App\Models\HorarioPrescripcion;
use App\Models\Medicamento;
use App\Models\Prescripcion;
use App\Models\Residente;
use App\Models\User;
use App\Modulos\Clinica\Aplicacion\Acciones\AccesoClinicoTemporalService;
use App\Modulos\Clinica\Aplicacion\Acciones\AutorizacionClinicaService;
use App\Modulos\Cuidados\Aplicacion\Consultas\TurnoEnfermeriaService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/** Conserva los contratos de entrada existentes y sus límites transaccionales. */
final class GestionarPrescripciones
{
    public function guardarDesdePanel(User $usuario, array $datos): Prescripcion
    {
        $this->autorizarOrden($usuario, 'prescripciones.crear');
        $residente = Residente::query()->findOrFail($datos['nuevo_cod_residente']);
        $this->autorizarResidente($residente, $usuario);
        $personal = $usuario?->personal()->where('estado', 'ACTIVO')->first();
        if (! $personal) {
            throw ValidationException::withMessages(['nuevo_medico' => 'El usuario médico no tiene un registro de personal activo.']);
        }

        $atencion = Atencion::query()
            ->where('cod_residente', $residente->cod_residente)
            ->where('cod_personal', $personal->cod_personal)
            ->whereNotIn('estado', ['ANULADA', 'CANCELADA'])
            ->latest('fecha_hora')
            ->first();
        if (! $atencion) {
            throw ValidationException::withMessages([
                'nuevo_cod_residente' => 'Registre primero una atención médica del residente; la BDD V2 exige vincular cada prescripción a una atención.',
            ]);
        }

        [$dosis, $unidad] = $this->separarDosis($datos['nuevo_dosis']);
        $prescripcion = DB::transaction(function () use ($residente, $personal, $atencion, $dosis, $unidad, $datos): Prescripcion {
            $medicamento = Medicamento::query()->firstOrCreate(
                ['nombre_generico' => Str::upper(trim($datos['nuevo_nombre']))],
                [
                    'cod_medicamento' => $this->codigo('MED'),
                    'via_predeterminada' => Str::upper(trim($datos['nuevo_via'])),
                    'control_especial' => false,
                    'estado' => 'ACTIVO',
                ],
            );

            $observacion = trim($datos['nuevo_observacion']);
            if (filled($datos['nuevo_fecha_fin'])) {
                $observacion = trim($observacion."\nFecha de finalización prevista: {$datos['nuevo_fecha_fin']}");
            }

            $prescripcion = Prescripcion::query()->create([
                'cod_prescripcion' => $this->codigo('PRE'),
                'cod_residente' => $residente->cod_residente,
                'cod_atencion' => $atencion->cod_atencion,
                'cod_medicamento' => $medicamento->cod_medicamento,
                'cod_personal' => $personal->cod_personal,
                'dosis' => $dosis,
                'unidad_dosis' => $unidad,
                'via_administracion' => Str::upper(trim($datos['nuevo_via'])),
                'frecuencia' => trim($datos['nuevo_frecuencia']),
                'indicacion' => null,
                'segun_necesidad' => false,
                'fecha_hora_prescripcion' => Carbon::parse($datos['nuevo_fecha_inicio'].' '.now()->format('H:i:s')),
                'estado' => 'ACTIVA',
                'observacion' => $observacion !== '' ? $observacion : null,
            ]);

            HorarioPrescripcion::query()->create([
                'cod_horario_prescripcion' => $this->codigo('HPR'),
                'cod_prescripcion' => $prescripcion->cod_prescripcion,
                'hora_programada' => $datos['nuevo_hora'],
                'dosis_programada' => $dosis,
                'estado' => 'ACTIVO',
            ]);

            return $prescripcion;
        });

        return $prescripcion;
    }

    public function guardarDesdeModal(User $usuario, array $datos): void
    {
        $this->autorizarOrden($usuario, $datos['isEditing'] ? 'prescripciones.editar' : 'prescripciones.crear');
        $personal = $usuario?->personal()->where('estado', 'ACTIVO')->first();
        if (! $personal) {
            throw ValidationException::withMessages(['medico_indica' => 'El usuario médico no tiene un registro de personal activo.']);
        }
        [$cantidad, $unidad] = $this->separarDosis($datos['dosis']);

        DB::transaction(function () use ($personal, $cantidad, $unidad, $datos): void {
            $medicamento = Medicamento::query()->firstOrCreate(
                ['nombre_generico' => Str::upper(trim($datos['nombre_medicamento']))],
                [
                    'cod_medicamento' => $this->codigo('MED'),
                    'via_predeterminada' => Str::upper($datos['via_administracion']),
                    'control_especial' => false,
                    'estado' => 'ACTIVO',
                ],
            );
            $observacion = trim($datos['observacion']);
            if (filled($datos['fecha_fin'])) {
                $observacion = trim($observacion."\nFecha de finalización prevista: {$datos['fecha_fin']}");
            }
            if ($datos['es_prn'] && $datos['intervalo_horas']) {
                $observacion = trim($observacion."\nIntervalo PRN indicado: {$datos['intervalo_horas']} horas.");
            }

            if ($datos['isEditing']) {
                $prescripcion = Prescripcion::query()->where('cod_residente', $datos['cod_residente'])
                    ->findOrFail($datos['cod_prescripcion']);
            } else {
                $atencion = Atencion::query()->where('cod_residente', $datos['cod_residente'])
                    ->where('cod_personal', $personal->cod_personal)
                    ->whereNotIn('estado', ['ANULADA', 'CANCELADA'])
                    ->latest('fecha_hora')->first();
                if (! $atencion) {
                    throw ValidationException::withMessages([
                        'cod_residente' => 'Registre primero una atención médica; la prescripción V2 debe pertenecer a una atención.',
                    ]);
                }
                $prescripcion = new Prescripcion([
                    'cod_prescripcion' => $this->codigo('PRE'),
                    'cod_residente' => $datos['cod_residente'],
                    'cod_atencion' => $atencion->cod_atencion,
                    'cod_personal' => $personal->cod_personal,
                ]);
            }

            $prescripcion->fill([
                'cod_medicamento' => $medicamento->cod_medicamento,
                'dosis' => $cantidad,
                'unidad_dosis' => $unidad,
                'via_administracion' => Str::upper(trim($datos['via_administracion'])),
                'frecuencia' => trim($datos['frecuencia']),
                'indicacion' => $datos['es_prn'] ? trim($datos['condicion_prn']) : null,
                'segun_necesidad' => $datos['es_prn'],
                'fecha_hora_prescripcion' => Carbon::parse($datos['fecha_inicio'].' '.now()->format('H:i:s')),
                'estado' => $this->estadoV2($datos['estado']),
                'observacion' => $observacion !== '' ? $observacion : null,
            ])->save();

            $horario = $prescripcion->horarios()->first();
            if ($datos['es_prn']) {
                $horario?->update(['estado' => 'INACTIVO']);
            } else {
                $datosHorario = [
                    'hora_programada' => $datos['hora_programada'],
                    'dosis_programada' => $cantidad,
                    'estado' => 'ACTIVO',
                ];
                $horario
                    ? $horario->update($datosHorario)
                    : HorarioPrescripcion::query()->create([
                        'cod_horario_prescripcion' => $this->codigo('HPR'),
                        'cod_prescripcion' => $prescripcion->cod_prescripcion,
                        ...$datosHorario,
                    ]);
            }
        });

    }

    public function suspenderMedicamento(User $usuario, string $id): Prescripcion
    {
        $this->autorizarOrden($usuario, 'prescripciones.crear');
        $prescripcion = Prescripcion::query()->with(['adultoMayor', 'medicamento'])->findOrFail($id);
        $this->autorizarResidente($prescripcion->adultoMayor, $usuario);
        abort_unless($prescripcion->estado === 'ACTIVA', 409, 'Solo puede suspender una prescripción activa.');
        $prescripcion->update([
            'cod_personal_suspension' => $usuario->personal->cod_personal,
            'fecha_hora_suspension' => now(),
            'motivo_suspension' => 'Suspendida desde el panel clínico.',
            'estado' => 'SUSPENDIDA',
        ]);

        return $prescripcion;
    }

    public function finalizarMedicamento(User $usuario, string $id): Prescripcion
    {
        $this->autorizarOrden($usuario, 'prescripciones.crear');
        $prescripcion = Prescripcion::query()->with(['adultoMayor', 'medicamento'])->findOrFail($id);
        $this->autorizarResidente($prescripcion->adultoMayor, $usuario);
        abort_unless($prescripcion->estado === 'ACTIVA', 409, 'Solo puede finalizar una prescripción activa.');
        $prescripcion->update(['estado' => 'FINALIZADA']);

        return $prescripcion;
    }

    public function cambiarEstadoModal(User $usuario, string $codResidente, string $id, string $estado): void
    {
        $this->autorizarOrden($usuario, 'prescripciones.editar');
        validator(['estado' => $estado], ['estado' => ['required', Rule::in(['ACTIVO', 'ACTIVA', 'SUSPENDIDO', 'SUSPENDIDA', 'FINALIZADO', 'FINALIZADA'])]])->validate();
        Prescripcion::query()->where('cod_residente', $codResidente)->findOrFail($id)
            ->update(['estado' => $this->estadoV2($estado)]);
    }

    public function prescribir(User $usuario, Residente $residente, array $datos): Prescripcion
    {
        Gate::forUser($usuario)->authorize('create', Prescripcion::class);
        $personal = app(AutorizacionClinicaService::class)->autorizarMutacion($usuario, $residente, 'prescripciones.crear', ['MEDICO GENERAL/GERIATRA']);
        abort_unless(Atencion::query()->whereKey($datos['cod_atencion'])->where('cod_residente', $residente->cod_residente)->exists(), 422, 'La atención no pertenece al residente.');
        $horarios = $datos['horarios'] ?? [];
        unset($datos['horarios']);
        abort_if(! $datos['segun_necesidad'] && $horarios === [], 422,
            'Una prescripción programada requiere al menos un horario.');
        $prescripcion = DB::transaction(function () use ($residente, $personal, $datos, $horarios): Prescripcion {
            $prescripcion = Prescripcion::query()->create(['cod_prescripcion' => $this->codigo('PRE'), 'cod_residente' => $residente->cod_residente, 'cod_personal' => $personal->cod_personal, ...$datos, 'fecha_hora_prescripcion' => now(), 'estado' => 'ACTIVA']);
            foreach ($horarios as $horario) {
                HorarioPrescripcion::query()->create(['cod_horario_prescripcion' => $this->codigo('HPR'), 'cod_prescripcion' => $prescripcion->cod_prescripcion, ...$horario, 'estado' => 'ACTIVO']);
            }

            return $prescripcion;
        });

        return $prescripcion;
    }

    public function suspender(User $usuario, Prescripcion $prescripcion, array $datos): Prescripcion
    {
        Gate::forUser($usuario)->authorize('update', $prescripcion);
        $personal = app(AutorizacionClinicaService::class)->autorizarMutacion($usuario, $prescripcion->residente()->firstOrFail(), 'prescripciones.suspender', ['MEDICO GENERAL/GERIATRA']);
        $prescripcion = DB::transaction(function () use ($prescripcion, $personal, $datos): Prescripcion {
            $bloqueada = Prescripcion::query()->lockForUpdate()->findOrFail($prescripcion->getKey());
            abort_unless(in_array($bloqueada->estado, ['ACTIVA', 'ACTIVO'], true), 409,
                'La prescripción ya no se encuentra activa.');
            $bloqueada->update([...$datos, 'cod_personal_suspension' => $personal->cod_personal, 'fecha_hora_suspension' => now(), 'estado' => 'SUSPENDIDA']);

            return $bloqueada;
        });

        return $prescripcion;
    }

    private function autorizarOrden(User $usuario, string $permiso): void
    {
        abort_unless(app(AccesoClinicoTemporalService::class)->tieneRol($usuario, ['MEDICO GENERAL/GERIATRA'])
            && $usuario->can($permiso), 403, 'Las órdenes médicas solo pueden ser creadas o modificadas por personal médico autorizado.');
    }

    private function autorizarResidente(Residente $adulto, User $usuario): void
    {
        if ($usuario->hasRole('ENFERMEROS')) {
            app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($adulto, $usuario);
        }
    }

    private function codigo(string $prefijo): string
    {
        return $prefijo.'_'.Str::upper(Str::random(12));
    }

    private function separarDosis(string $texto): array
    {
        preg_match('/(\d+(?:[.,]\d+)?)/', $texto, $coincidencia);
        $dosis = isset($coincidencia[1]) ? (float) str_replace(',', '.', $coincidencia[1]) : 1;
        $unidad = trim(preg_replace('/^\s*'.preg_quote($coincidencia[1] ?? '', '/').'\s*/', '', $texto));

        return [$dosis, Str::limit($unidad !== '' ? $unidad : 'DOSIS', 30, '')];
    }

    private function estadoV2(string $estado): string
    {
        return match ($estado) {
            'ACTIVO' => 'ACTIVA',
            'SUSPENDIDO' => 'SUSPENDIDA',
            'FINALIZADO' => 'FINALIZADA',
            'ARCHIVADO' => 'ARCHIVADA',
            default => $estado,
        };
    }
}
