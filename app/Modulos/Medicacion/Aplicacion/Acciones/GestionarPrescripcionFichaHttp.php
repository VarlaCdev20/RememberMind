<?php

namespace App\Modulos\Medicacion\Aplicacion\Acciones;

use App\Models\Atencion;
use App\Models\HorarioPrescripcion;
use App\Models\Medicamento;
use App\Models\Prescripcion;
use App\Models\Residente;
use App\Models\User;
use App\Modulos\Clinica\Aplicacion\Consultas\ContextoClinicoService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class GestionarPrescripcionFichaHttp
{
    public function store(User $usuario, array $entrada, Residente $adulto_mayor, ContextoClinicoService $contextoClinico): mixed
    {
        abort_unless(! $usuario->hasRole('ENFERMEROS') && $usuario->can('prescripciones.crear'), 403);
        try {
            DB::beginTransaction();
            $datos = $entrada;
            $nombreMed = $datos['nombre_medicamento'];
            // 1. Resolver Medicamento
            $nombreNormalizado = mb_strtolower($nombreMed);
            $medicamento = Medicamento::query()->whereRaw('LOWER(nombre_generico) = ?', [$nombreNormalizado])->orWhereRaw('LOWER(nombre_comercial) = ?', [$nombreNormalizado])->first();
            if (! $medicamento) {
                $medicamento = Medicamento::create(['cod_medicamento' => app(\App\Compartido\Aplicacion\Contratos\CodigosEntidad::class)->siguiente('medicamentos'), 'nombre_generico' => $nombreMed, 'nombre_comercial' => $nombreMed, 'control_especial' => false, 'estado' => 'ACTIVO']);
            }
            // 2. Resolver Personal y Atención médica
            $personal = $contextoClinico->personalActivo($usuario);
            $codPersonal = $personal->cod_personal;
            $codArea = $contextoClinico->areaAtencion($personal)->cod_area;
            $atencion = Atencion::create(['cod_atencion' => app(\App\Compartido\Aplicacion\Contratos\CodigosEntidad::class)->siguiente('atenciones'), 'cod_residente' => $adulto_mayor->cod_residente, 'cod_area' => $codArea, 'cod_personal' => $codPersonal, 'tipo_atencion' => 'PRESCRIPCION_MEDICA', 'motivo' => 'Prescripción médica farmacológica', 'fecha_hora' => now(), 'estado' => 'COMPLETADA']);
            // 3. Crear Prescripción V2
            $dosisNum = 1.0;
            if (isset($datos['dosis']) && preg_match('/(\d+(?:\.\d+)?)/', (string) $datos['dosis'], $m)) {
                $dosisNum = (float) $m[1];
            }
            $codPrescripcion = app(\App\Compartido\Aplicacion\Contratos\CodigosEntidad::class)->siguiente('prescripciones');
            $prescripcion = Prescripcion::create(['cod_prescripcion' => $codPrescripcion, 'cod_residente' => $adulto_mayor->cod_residente, 'cod_atencion' => $atencion->cod_atencion, 'cod_medicamento' => $medicamento->cod_medicamento, 'cod_personal' => $codPersonal, 'dosis' => $dosisNum, 'unidad_dosis' => 'unidad', 'via_administracion' => $datos['via_administracion'] ?? 'ORAL', 'frecuencia' => $datos['frecuencia'] ?? 'Cada 24 horas', 'indicacion' => $nombreMed.(! empty($datos['observacion']) ? " - {$datos['observacion']}" : ''), 'segun_necesidad' => false, 'fecha_hora_prescripcion' => now(), 'estado' => 'ACTIVA']);
            // 4. Crear Horario si fue indicado
            if (! empty($datos['hora_programada'])) {
                HorarioPrescripcion::create(['cod_horario_prescripcion' => app(\App\Compartido\Aplicacion\Contratos\CodigosEntidad::class)->siguiente('horarios_prescripcion'), 'cod_prescripcion' => $codPrescripcion, 'hora_programada' => substr($datos['hora_programada'], 0, 5), 'estado' => 'ACTIVO']);
            }
            DB::commit();

            return $nombreMed;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update(User $usuario, array $entrada, Residente $adulto_mayor, Prescripcion $medicacion): mixed
    {
        abort_unless(! $usuario->hasRole('ENFERMEROS') && $usuario->can('prescripciones.editar'), 403);
        abort_unless($medicacion->cod_residente === $adulto_mayor->cod_residente, 404);
        try {
            DB::beginTransaction();
            $datos = $entrada;
            $dosisNum = $medicacion->dosis;
            if (isset($datos['dosis']) && preg_match('/(\d+(?:\.\d+)?)/', (string) $datos['dosis'], $m)) {
                $dosisNum = (float) $m[1];
            }
            $medicacion->update(['dosis' => $dosisNum, 'via_administracion' => $datos['via_administracion'] ?? $medicacion->via_administracion, 'frecuencia' => $datos['frecuencia'] ?? $medicacion->frecuencia, 'indicacion' => $datos['nombre_medicamento'] ?? $medicacion->indicacion]);
            if (! empty($datos['hora_programada'])) {
                $horario = $medicacion->horarios()->first();
                if ($horario) {
                    $horario->update(['hora_programada' => substr($datos['hora_programada'], 0, 5)]);
                } else {
                    HorarioPrescripcion::create(['cod_horario_prescripcion' => app(\App\Compartido\Aplicacion\Contratos\CodigosEntidad::class)->siguiente('horarios_prescripcion'), 'cod_prescripcion' => $medicacion->cod_prescripcion, 'hora_programada' => substr($datos['hora_programada'], 0, 5), 'estado' => 'ACTIVO']);
                }
            }
            DB::commit();

            return null;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function suspender(User $usuario, array $entrada, Residente $adulto_mayor, Prescripcion $medicacion): mixed
    {
        $this->autorizarOrden($usuario, $adulto_mayor, $medicacion, ['prescripciones.suspender']);
        $medicacion->update(['estado' => 'SUSPENDIDA', 'fecha_hora_suspension' => now(), 'motivo_suspension' => 'Suspensión indicada por facultativo']);
        activity('Medicación')->causedBy($usuario)->performedOn($medicacion)->event('suspended')->log("Se suspendió la prescripción {$medicacion->cod_prescripcion} del residente {$adulto_mayor->cod_residente}.");

        return null;
    }

    public function finalizar(User $usuario, array $entrada, Residente $adulto_mayor, Prescripcion $medicacion): mixed
    {
        $this->autorizarOrden($usuario, $adulto_mayor, $medicacion, ['prescripciones.editar']);
        $medicacion->update(['estado' => 'FINALIZADA', 'fecha_hora_suspension' => now()]);
        activity('Medicación')->causedBy($usuario)->performedOn($medicacion)->event('finalized')->log("Se finalizó la prescripción {$medicacion->cod_prescripcion} del residente {$adulto_mayor->cod_residente}.");

        return null;
    }

    public function archivar(User $usuario, array $entrada, Residente $adulto_mayor, Prescripcion $medicacion): mixed
    {
        $this->autorizarOrden($usuario, $adulto_mayor, $medicacion, ['prescripciones.editar']);
        $medicacion->update(['estado' => 'ANULADA']);

        return null;
    }

    public function restore(User $usuario, array $entrada, Residente $adulto_mayor, $medicacion): mixed
    {
        $med = Prescripcion::findOrFail($medicacion);
        $this->autorizarOrden($usuario, $adulto_mayor, $med, ['prescripciones.editar']);
        $med->update(['estado' => 'ACTIVA']);

        return null;
    }

    private function autorizarOrden(User $usuario, Residente $adulto, Prescripcion $medicacion, array $permisos): void
    {
        abort_unless($medicacion->cod_residente === $adulto->cod_residente, 404);
        abort_if($usuario?->hasRole('ENFERMEROS'), 403, 'Enfermería no puede modificar órdenes médicas.');
        abort_unless($usuario?->canAny($permisos), 403);
    }
}
