<?php

namespace App\Http\Controllers\Clinica;

use App\Http\Controllers\Controller;
use App\Http\Requests\Clinica\StoreFichaMedicaRequest;
use App\Http\Requests\Clinica\UpdateFichaMedicaRequest;
use App\Models\AdultoMayor;
use App\Models\FichaMedicaAdulto;
use Illuminate\Support\Facades\DB;

class AdultoMayorFichaMedicaController extends Controller
{
    public function store(StoreFichaMedicaRequest $request, AdultoMayor $adulto_mayor)
    {
        try {
            DB::beginTransaction();

            $ficha = FichaMedicaAdulto::create(array_merge(
                $request->validated(),
                [
                    'cod_am'         => $adulto_mayor->cod_am,
                    'registrado_por' => auth()->user()->cod_usu,
                ]
            ));

            DB::commit();

            return redirect()
                ->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_am, 'tab' => 'ficha-medica'])
                ->with('success', 'Ficha médica registrada correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al registrar ficha médica: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function update(UpdateFichaMedicaRequest $request, AdultoMayor $adulto_mayor, FichaMedicaAdulto $ficha)
    {
        try {
            DB::beginTransaction();

            $ficha->update($request->validated());

            DB::commit();

            return redirect()
                ->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_am, 'tab' => 'ficha-medica'])
                ->with('success', 'Ficha médica actualizada correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al actualizar ficha médica: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function archivar(AdultoMayor $adulto_mayor, FichaMedicaAdulto $ficha)
    {
        $ficha->update(['estado' => 'ARCHIVADO']);
        $ficha->delete(); // SoftDelete

        activity('Ficha Médica')
            ->causedBy(auth()->user())
            ->performedOn($ficha)
            ->event('archived')
            ->log("Se archivó la ficha médica del adulto mayor {$adulto_mayor->cod_am}.");

        return redirect()
            ->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_am, 'tab' => 'ficha-medica'])
            ->with('success', 'Ficha médica archivada correctamente.');
    }

    public function restore(AdultoMayor $adulto_mayor, $ficha)
    {
        $fichaRestaurada = FichaMedicaAdulto::withTrashed()->findOrFail($ficha);
        $fichaRestaurada->restore();
        $fichaRestaurada->update(['estado' => 'ACTIVO']);

        activity('Ficha Médica')
            ->causedBy(auth()->user())
            ->performedOn($fichaRestaurada)
            ->event('restored')
            ->log("Se restauró la ficha médica del adulto mayor {$adulto_mayor->cod_am}.");

        return redirect()
            ->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_am, 'tab' => 'ficha-medica'])
            ->with('success', 'Ficha médica restaurada correctamente.');
    }
}
