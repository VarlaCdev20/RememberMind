<?php

namespace App\Modulos\Medicacion\Presentacion\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Residente;
use App\Modulos\Medicacion\Aplicacion\Acciones\RegistrarAdministracionFichaHttp;
use App\Modulos\Medicacion\Aplicacion\Acciones\RegistrarAdministracionMedicacionService;
use App\Modulos\Medicacion\Presentacion\Http\Requests\StoreAdministracionMedicacionRequest;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class AdultoMayorAdministracionMedicacionController extends Controller
{
    public function store(StoreAdministracionMedicacionRequest $request, Residente $adulto_mayor, RegistrarAdministracionMedicacionService $servicio)
    {
        try {
            $tipoEvento = app(RegistrarAdministracionFichaHttp::class)->ejecutar(auth()->user(), $adulto_mayor, $request->validated(), $servicio);

            return redirect()
                ->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_residente, 'tab' => 'medicacion'])
                ->with('success', $tipoEvento.'.');

        } catch (\Throwable $e) {
            if ($e instanceof ValidationException
                || $e instanceof HttpExceptionInterface) {
                throw $e;
            }
            report($e);

            return redirect()->back()
                ->with('error', 'No se pudo registrar la administración. Inténtelo nuevamente.')
                ->withInput();
        }
    }
}
