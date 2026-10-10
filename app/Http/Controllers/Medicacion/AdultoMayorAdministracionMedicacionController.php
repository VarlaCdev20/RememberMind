<?php

namespace App\Http\Controllers\Medicacion;

use App\Backend\Modulos\Medicacion\Servicios\RegistrarAdministracionMedicacionService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Medicacion\StoreAdministracionMedicacionRequest;
use App\Models\Residente;

class AdultoMayorAdministracionMedicacionController extends Controller
{
    public function store(StoreAdministracionMedicacionRequest $request, Residente $adulto_mayor, RegistrarAdministracionMedicacionService $servicio)
    {
        try {
            $datos = $request->validated();
            $registro = $servicio->registrarProgramada(
                auth()->user(), $adulto_mayor->cod_residente, $datos['cod_prescripcion'],
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
                    'cod_prescripcion' => $registro->cod_prescripcion,
                    'administrado' => $registro->administrado,
                    'fecha' => $registro->fecha,
                ])
                ->log("{$tipoEvento} para el adulto mayor {$adulto_mayor->cod_residente}.");

            return redirect()
                ->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_residente, 'tab' => 'medicacion'])
                ->with('success', $tipoEvento.'.');

        } catch (\Throwable $e) {
            if ($e instanceof \Illuminate\Validation\ValidationException
                || $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                throw $e;
            }
            report($e);
            return redirect()->back()
                ->with('error', 'No se pudo registrar la administración. Inténtelo nuevamente.')
                ->withInput();
        }
    }
}
