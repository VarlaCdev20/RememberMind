<?php

namespace App\Http\Controllers\Admin\AdultosMayores\Salud;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdultosMayores\Salud\StoreSignosVitalesRequest;
use App\Models\AdultoMayor;
use App\Models\SignosVitalesAdulto;

class AdultoMayorSignosVitalesController extends Controller
{
    public function store(StoreSignosVitalesRequest $request, AdultoMayor $adulto_mayor)
    {
        try {
            $data = $request->validated();

            // Calcular IMC automáticamente si peso y talla están presentes
            if (!empty($data['peso']) && !empty($data['talla']) && $data['talla'] > 0) {
                $tallaMetros = $data['talla'] > 3 ? $data['talla'] / 100 : $data['talla'];
                $data['imc'] = round($data['peso'] / ($tallaMetros * $tallaMetros), 2);
            }

            $signo = SignosVitalesAdulto::create(array_merge(
                $data,
                [
                    'cod_am'         => $adulto_mayor->cod_am,
                    'registrado_por' => auth()->user()->cod_usu,
                ]
            ));

            return redirect()
                ->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_am, 'tab' => 'signos-vitales'])
                ->with('success', 'Signos vitales registrados correctamente.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al registrar signos vitales: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function update(StoreSignosVitalesRequest $request, AdultoMayor $adulto_mayor, SignosVitalesAdulto $signo)
    {
        try {
            $data = $request->validated();

            if (!empty($data['peso']) && !empty($data['talla']) && $data['talla'] > 0) {
                $tallaMetros = $data['talla'] > 3 ? $data['talla'] / 100 : $data['talla'];
                $data['imc'] = round($data['peso'] / ($tallaMetros * $tallaMetros), 2);
            }

            $signo->update($data);

            return redirect()
                ->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_am, 'tab' => 'signos-vitales'])
                ->with('success', 'Signos vitales actualizados correctamente.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al actualizar signos vitales: ' . $e->getMessage())
                ->withInput();
        }
    }
}
