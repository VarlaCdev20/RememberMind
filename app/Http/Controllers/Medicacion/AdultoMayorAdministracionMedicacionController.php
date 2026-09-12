<?php

namespace App\Http\Controllers\Medicacion;

use App\Http\Controllers\Controller;
use App\Http\Requests\Medicacion\StoreAdministracionMedicacionRequest;
use App\Models\AdultoMayor;
use App\Services\Medicacion\RegistrarAdministracionMedicacionService;

class AdultoMayorAdministracionMedicacionController extends Controller
{
    public function store(StoreAdministracionMedicacionRequest $request, AdultoMayor $adulto_mayor, RegistrarAdministracionMedicacionService $servicio)
    {
        try {
            $datos = $request->validated();
            $registro = $servicio->registrarProgramada(
                auth()->user(), $adulto_mayor->cod_am, $datos['cod_med_adulto'],
                $datos['hora_programada'], (bool) $datos['administrado'],
                $datos['motivo_omision'] ?? null, $datos['observacion'] ?? null,
            );

            $tipoEvento = $registro->administrado
                ? 'Se registró administración de medicación'
                : 'Se registró omisión de medicación';

            activity('Administración Medicación')
                ->causedBy(auth()->user())
                ->performedOn($registro)
                ->event($registro->administrado ? 'administrado' : 'omitido')
                ->withProperties([
                    'cod_med_adulto' => $registro->cod_med_adulto,
                    'administrado'   => $registro->administrado,
                    'fecha'          => $registro->fecha,
                ])
                ->log("{$tipoEvento} para el adulto mayor {$adulto_mayor->cod_am}.");

            return redirect()
                ->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_am, 'tab' => 'medicacion'])
                ->with('success', $tipoEvento . '.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al registrar administración: ' . $e->getMessage())
                ->withInput();
        }
    }
}
