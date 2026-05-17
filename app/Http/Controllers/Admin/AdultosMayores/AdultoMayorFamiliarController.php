<?php

namespace App\Http\Controllers\Admin\AdultosMayores;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdultosMayores\StoreFamiliarAdultoRequest;
use App\Models\AdultoMayor;
use App\Models\Familiar;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdultoMayorFamiliarController extends Controller
{
    public function index(AdultoMayor $adulto_mayor)
    {
        $familiares = $adulto_mayor->familiares;
        return view('admin.adultos-mayores.familiares.index', compact('adulto_mayor', 'familiares'));
    }

    public function store(StoreFamiliarAdultoRequest $request, AdultoMayor $adulto_mayor)
    {
        try {
            DB::beginTransaction();

            $codFam = $request->cod_fam;

            // Si es un familiar nuevo, crear usuario y registro de familiar
            if (!$codFam) {
                // Generar contraseña temporal segura basada en iniciales + fragmento aleatorio
                $passwordLimpia = $this->generarPasswordTemporal(
                    $request->nombres ?? $request->nombre_nuevo ?? 'FAMILIAR',
                    $request->ap_paterno ?? '',
                    $request->ap_materno ?? ''
                );

                // Crear usuario con los campos REALES del modelo User
                $user = User::create([
                    'nombres'           => $request->nombres ?? $request->nombre_nuevo ?? 'FAMILIAR',
                    'ap_paterno'        => $request->ap_paterno ?? null,
                    'ap_materno'        => $request->ap_materno ?? null,
                    'correo'            => $request->correo ?? $request->email_nuevo ?? $this->generarCorreoTemporal(),
                    'password'          => Hash::make($passwordLimpia),
                    'telefono'          => $request->telefono ?? null,
                    'estado'            => 'ACTIVO',
                    'acceso_sistema'    => 'HABILITADO',
                ]);
                $user->assignRole('familiar');

                // Crear registro en tabla familiares
                $familiar = Familiar::create([
                    'parentesco'    => $request->parentesco_vinculo,
                    'cod_usu'       => $user->cod_usu,
                    'es_responsable' => $request->es_responsable ? 'SI' : 'NO',
                ]);
                $codFam = $familiar->cod_fam;
            }

            // Vincular con el Adulto Mayor si no está ya vinculado
            if (!$adulto_mayor->familiares()->where('familiar_adulto.cod_fam', $codFam)->exists()) {
                $adulto_mayor->familiares()->attach($codFam, [
                    'parentesco_vinculo' => $request->parentesco_vinculo,
                    'es_responsable'     => $request->es_responsable ?? false,
                    'estado'             => $request->estado ?? 'ACTIVO',
                    'observaciones'      => $request->observaciones,
                ]);
            }

            DB::commit();

            activity('Adulto Mayor')
                ->performedOn($adulto_mayor)
                ->event('familiar_vinculado')
                ->withProperties(['cod_fam' => $codFam, 'cod_am' => $adulto_mayor->cod_am])
                ->log("Se vinculó un familiar al adulto mayor: {$adulto_mayor->nombres}");

            // Incluir contraseña temporal en mensaje de éxito (solo si se creó usuario nuevo)
            $mensaje = 'Familiar vinculado correctamente.';
            if (isset($passwordLimpia)) {
                $mensaje .= " Contraseña temporal del familiar: {$passwordLimpia} (anótela, no se mostrará de nuevo).";
            }

            return redirect()
                ->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_am, 'tab' => 'familiares'])
                ->with('success', $mensaje);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_am, 'tab' => 'familiares'])
                ->with('error', 'Error al vincular familiar: ' . $e->getMessage());
        }
    }

    /**
     * Genera una contraseña temporal segura: iniciales del nombre + 6 caracteres aleatorios.
     * Ejemplo: "JPA_x7Km3q"
     */
    private function generarPasswordTemporal(string $nombres, string $paterno, string $materno): string
    {
        $partes = array_filter([$nombres, $paterno, $materno]);
        $iniciales = '';
        foreach ($partes as $parte) {
            $iniciales .= mb_strtoupper(mb_substr(trim($parte), 0, 1));
        }

        return $iniciales . '_' . Str::random(6);
    }

    /**
     * Genera un correo temporal único cuando el familiar no tiene correo propio.
     */
    private function generarCorreoTemporal(): string
    {
        return 'familiar_' . Str::random(8) . '@casaamandita.temporal';
    }

    public function update(Request $request, AdultoMayor $adulto_mayor, $familiar)
    {
        $request->validate([
            'parentesco_vinculo' => 'required|string',
            'es_responsable' => 'boolean',
            'estado' => 'required|in:ACTIVO,INACTIVO',
        ]);

        $adulto_mayor->familiares()->updateExistingPivot($familiar, [
            'parentesco_vinculo' => $request->parentesco_vinculo,
            'es_responsable' => $request->es_responsable,
            'estado' => $request->estado,
            'observaciones' => $request->observaciones,
        ]);

        activity('Adulto Mayor')
            ->performedOn($adulto_mayor)
            ->event('updated')
            ->log("Se actualizó el vínculo del familiar con la ficha {$adulto_mayor->cod_am}.");

        return redirect()
            ->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_am, 'tab' => 'familiares'])
            ->with('success', 'Vínculo actualizado correctamente.');
    }

    public function destroy(AdultoMayor $adulto_mayor, $familiar)
    {
        // Desactivar vínculo en vez de eliminar (soft-disable)
        $adulto_mayor->familiares()->updateExistingPivot($familiar, [
            'estado' => 'INACTIVO'
        ]);

        activity('Adulto Mayor')
            ->performedOn($adulto_mayor)
            ->event('deleted')
            ->withProperties(['cod_fam' => $familiar])
            ->log("Se desactivó el vínculo del familiar con la ficha {$adulto_mayor->cod_am}.");

        return redirect()
            ->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_am, 'tab' => 'familiares'])
            ->with('success', 'Familiar desactivado correctamente.');
    }

    /**
     * Restaurar vínculo de familiar.
     */
    public function restore(AdultoMayor $adulto_mayor, $familiar)
    {
        $adulto_mayor->familiares()->updateExistingPivot($familiar, [
            'estado' => 'ACTIVO'
        ]);

        activity('Adulto Mayor')
            ->performedOn($adulto_mayor)
            ->event('restored')
            ->withProperties(['cod_fam' => $familiar])
            ->log("Se restauró el vínculo con el familiar.");

        return redirect()
            ->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_am, 'tab' => 'familiares'])
            ->with('success', 'Vínculo restaurado correctamente.');
    }
}
