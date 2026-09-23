<?php

namespace App\Http\Controllers\Medicacion;

use App\Http\Controllers\Controller;
use App\Http\Requests\Medicacion\StoreMedicacionRequest;
use App\Http\Requests\Medicacion\UpdateMedicacionRequest;
use App\Models\Area;
use App\Models\Atencion;
use App\Models\HorarioPrescripcion;
use App\Models\Medicamento;
use App\Models\Personal;
use App\Models\Prescripcion;
use App\Models\Residente;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdultoMayorMedicacionController extends Controller
{
    public function store(StoreMedicacionRequest $request, Residente $adulto_mayor)
    {
        try {
            DB::beginTransaction();

            $datos = $request->validated();
            $nombreMed = $datos['nombre_medicamento'];

            // 1. Resolver Medicamento
            $medicamento = Medicamento::where('nombre_generico', 'ilike', $nombreMed)
                ->orWhere('nombre_comercial', 'ilike', $nombreMed)
                ->first();

            if (!$medicamento) {
                $medicamento = Medicamento::create([
                    'cod_medicamento' => 'MED_' . strtoupper(Str::random(10)),
                    'nombre_generico' => $nombreMed,
                    'nombre_comercial'=> $nombreMed,
                    'control_especial'=> false,
                    'estado'          => 'ACTIVO',
                ]);
            }

            // 2. Resolver Personal y Atención médica
            $codPersonal = Personal::where('cod_usuario', auth()->user()->cod_usuario)->value('cod_personal')
                ?? Personal::value('cod_personal')
                ?? 'PER_0001';

            $atencion = Atencion::where('cod_residente', $adulto_mayor->cod_residente)->latest('fecha_hora')->first();
            if (!$atencion) {
                $codArea = Area::where('nombre', 'like', '%Med%')->value('cod_area') ?? 'ARE_0001';
                $atencion = Atencion::create([
                    'cod_atencion'  => 'ATN_' . strtoupper(Str::random(10)),
                    'cod_residente' => $adulto_mayor->cod_residente,
                    'cod_area'      => $codArea,
                    'cod_personal'  => $codPersonal,
                    'tipo_atencion' => 'CONSULTA',
                    'motivo'        => 'Prescripción médica farmacológica',
                    'fecha_hora'    => now(),
                    'estado'        => 'COMPLETADA',
                ]);
            }

            // 3. Crear Prescripción V2
            $dosisNum = 1.0;
            if (isset($datos['dosis']) && preg_match('/(\d+(?:\.\d+)?)/', (string)$datos['dosis'], $m)) {
                $dosisNum = (float) $m[1];
            }

            $codPrescripcion = 'PRE_' . strtoupper(Str::random(10));
            $prescripcion = Prescripcion::create([
                'cod_prescripcion'        => $codPrescripcion,
                'cod_residente'           => $adulto_mayor->cod_residente,
                'cod_atencion'            => $atencion->cod_atencion,
                'cod_medicamento'         => $medicamento->cod_medicamento,
                'cod_personal'            => $codPersonal,
                'dosis'                   => $dosisNum,
                'unidad_dosis'            => 'unidad',
                'via_administracion'      => $datos['via_administracion'] ?? 'ORAL',
                'frecuencia'              => $datos['frecuencia'] ?? 'Cada 24 horas',
                'indicacion'              => $nombreMed . ($datos['observacion'] ? " - {$datos['observacion']}" : ''),
                'segun_necesidad'         => false,
                'fecha_hora_prescripcion' => now(),
                'estado'                  => 'ACTIVA',
            ]);

            // 4. Crear Horario si fue indicado
            if (!empty($datos['hora_programada'])) {
                HorarioPrescripcion::create([
                    'cod_horario_prescripcion' => 'HPR_' . strtoupper(Str::random(10)),
                    'cod_prescripcion'         => $codPrescripcion,
                    'hora_programada'          => substr($datos['hora_programada'], 0, 5),
                    'estado'                   => 'ACTIVO',
                ]);
            }

            DB::commit();

            return redirect()
                ->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_residente, 'tab' => 'medicacion'])
                ->with('success', "Prescripción de '{$nombreMed}' registrada correctamente.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al registrar medicación: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function update(UpdateMedicacionRequest $request, Residente $adulto_mayor, Prescripcion $medicacion)
    {
        abort_unless($medicacion->cod_residente === $adulto_mayor->cod_residente, 404);
        try {
            DB::beginTransaction();

            $datos = $request->validated();
            $dosisNum = $medicacion->dosis;
            if (isset($datos['dosis']) && preg_match('/(\d+(?:\.\d+)?)/', (string)$datos['dosis'], $m)) {
                $dosisNum = (float) $m[1];
            }

            $medicacion->update([
                'dosis'              => $dosisNum,
                'via_administracion' => $datos['via_administracion'] ?? $medicacion->via_administracion,
                'frecuencia'         => $datos['frecuencia'] ?? $medicacion->frecuencia,
                'indicacion'         => $datos['nombre_medicamento'] ?? $medicacion->indicacion,
            ]);

            if (!empty($datos['hora_programada'])) {
                $horario = $medicacion->horarios()->first();
                if ($horario) {
                    $horario->update(['hora_programada' => substr($datos['hora_programada'], 0, 5)]);
                } else {
                    HorarioPrescripcion::create([
                        'cod_horario_prescripcion' => 'HPR_' . strtoupper(Str::random(10)),
                        'cod_prescripcion'         => $medicacion->cod_prescripcion,
                        'hora_programada'          => substr($datos['hora_programada'], 0, 5),
                        'estado'                   => 'ACTIVO',
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_residente, 'tab' => 'medicacion'])
                ->with('success', "Prescripción actualizada correctamente.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al actualizar medicación: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function suspender(Residente $adulto_mayor, Prescripcion $medicacion)
    {
        $this->autorizarOrden($adulto_mayor, $medicacion, ['medicacion.suspender', 'salud.medicacion.suspender']);
        $medicacion->update([
            'estado'                 => 'SUSPENDIDA',
            'fecha_hora_suspension'  => now(),
            'motivo_suspension'      => 'Suspensión indicada por facultativo',
        ]);

        activity('Medicación')
            ->causedBy(auth()->user())
            ->performedOn($medicacion)
            ->event('suspended')
            ->log("Se suspendió la prescripción {$medicacion->cod_prescripcion} del residente {$adulto_mayor->cod_residente}.");

        return redirect()
            ->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_residente, 'tab' => 'medicacion'])
            ->with('success', "Prescripción suspendida.");
    }

    public function finalizar(Residente $adulto_mayor, Prescripcion $medicacion)
    {
        $this->autorizarOrden($adulto_mayor, $medicacion, ['medicacion.suspender', 'salud.medicacion.finalizar']);
        $medicacion->update([
            'estado'                => 'FINALIZADA',
            'fecha_hora_suspension' => now(),
        ]);

        activity('Medicación')
            ->causedBy(auth()->user())
            ->performedOn($medicacion)
            ->event('finalized')
            ->log("Se finalizó la prescripción {$medicacion->cod_prescripcion} del residente {$adulto_mayor->cod_residente}.");

        return redirect()
            ->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_residente, 'tab' => 'medicacion'])
            ->with('success', "Prescripción finalizada.");
    }

    public function archivar(Residente $adulto_mayor, Prescripcion $medicacion)
    {
        $this->autorizarOrden($adulto_mayor, $medicacion, ['medicacion.suspender', 'salud.medicacion.anular']);
        $medicacion->update(['estado' => 'ANULADA']);

        return redirect()
            ->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_residente, 'tab' => 'medicacion'])
            ->with('success', "Prescripción archivada.");
    }

    public function restore(Residente $adulto_mayor, $medicacion)
    {
        $med = Prescripcion::findOrFail($medicacion);
        $this->autorizarOrden($adulto_mayor, $med, ['medicacion.editar', 'salud.medicacion.editar']);
        $med->update(['estado' => 'ACTIVA']);

        return redirect()
            ->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_residente, 'tab' => 'medicacion'])
            ->with('success', "Prescripción reactivada.");
    }

    private function autorizarOrden(Residente $adulto, Prescripcion $medicacion, array $permisos): void
    {
        abort_unless($medicacion->cod_residente === $adulto->cod_residente, 404);
        abort_if(auth()->user()?->hasRole('ENFERMEROS'), 403, 'Enfermería no puede modificar órdenes médicas.');
        abort_unless(auth()->user()?->canAny($permisos), 403);
    }
}