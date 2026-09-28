<?php

namespace App\Http\Controllers\Residentes;

use App\Http\Controllers\Controller;
use App\Http\Requests\Residentes\StoreFamiliarAdultoRequest;
use App\Models\AdultoMayor;
use App\Models\Contacto;
use App\Models\ResidenteContacto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AdultoMayorFamiliarController extends Controller
{
    public function index(AdultoMayor $adulto_mayor): RedirectResponse
    {
        return $this->volver($adulto_mayor);
    }

    public function store(StoreFamiliarAdultoRequest $request, AdultoMayor $adulto_mayor): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $adulto_mayor): void {
            $contacto = filled($data['cod_fam'] ?? null)
                ? Contacto::query()->findOrFail($data['cod_fam'])
                : $this->crearContacto($data);

            if (! empty($data['es_responsable'])) {
                ResidenteContacto::query()
                    ->where('cod_residente', $adulto_mayor->cod_residente)
                    ->update(['responsable_principal' => false]);
            }

            $vinculo = ResidenteContacto::query()->firstOrNew([
                'cod_residente' => $adulto_mayor->cod_residente,
                'cod_contacto' => $contacto->cod_contacto,
            ]);
            if (! $vinculo->exists) {
                $vinculo->cod_residente_contacto = 'RC_' . Str::upper(Str::random(10));
            }
            $vinculo->fill([
                'parentesco' => $data['parentesco_vinculo'],
                'responsable_principal' => (bool) $data['es_responsable'],
                'contacto_emergencia' => (bool) $data['es_responsable'],
                'autoriza_informacion' => true,
                'autoriza_salida' => false,
                'estado' => $data['estado'],
                'observacion' => $data['observaciones'] ?? null,
            ])->save();
        });

        activity('Residentes')
            ->performedOn($adulto_mayor)
            ->event('contacto_vinculado')
            ->log("Se vinculó un contacto al residente {$adulto_mayor->cod_residente}.");

        return $this->volver($adulto_mayor)->with('success', 'Contacto vinculado correctamente.');
    }

    public function update(Request $request, AdultoMayor $adulto_mayor, string $familiar): RedirectResponse
    {
        $data = $request->validate([
            'parentesco_vinculo' => ['required', 'string', 'max:40'],
            'es_responsable' => ['required', 'boolean'],
            'estado' => ['required', 'in:ACTIVO,INACTIVO'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($data, $adulto_mayor, $familiar): void {
            $vinculo = $this->vinculo($adulto_mayor, $familiar);
            if ($data['es_responsable']) {
                ResidenteContacto::query()
                    ->where('cod_residente', $adulto_mayor->cod_residente)
                    ->where('cod_residente_contacto', '!=', $vinculo->cod_residente_contacto)
                    ->update(['responsable_principal' => false]);
            }
            $vinculo->update([
                'parentesco' => $data['parentesco_vinculo'],
                'responsable_principal' => (bool) $data['es_responsable'],
                'estado' => $data['estado'],
                'observacion' => $data['observaciones'] ?? null,
            ]);
        });

        return $this->volver($adulto_mayor)->with('success', 'Vínculo actualizado correctamente.');
    }

    public function destroy(AdultoMayor $adulto_mayor, string $familiar): RedirectResponse
    {
        $this->vinculo($adulto_mayor, $familiar)->update(['estado' => 'INACTIVO']);

        return $this->volver($adulto_mayor)->with('success', 'Vínculo desactivado correctamente.');
    }

    public function restore(AdultoMayor $adulto_mayor, string $familiar): RedirectResponse
    {
        $this->vinculo($adulto_mayor, $familiar)->update(['estado' => 'ACTIVO']);

        return $this->volver($adulto_mayor)->with('success', 'Vínculo restaurado correctamente.');
    }

    private function crearContacto(array $data): Contacto
    {
        $partes = preg_split('/\s+/u', trim($data['nombre_nuevo'])) ?: [];
        $apellido = count($partes) > 1 ? array_pop($partes) : 'SIN APELLIDO';
        $nombres = trim(implode(' ', $partes)) ?: trim($data['nombre_nuevo']);

        return Contacto::query()->create([
            'cod_contacto' => 'CON_' . Str::upper(Str::random(10)),
            'nombres' => $nombres,
            'apellido_paterno' => $apellido,
            'correo' => $data['email_nuevo'] ?? null,
            'estado' => 'ACTIVO',
        ]);
    }

    private function vinculo(AdultoMayor $adulto, string $id): ResidenteContacto
    {
        return ResidenteContacto::query()
            ->where('cod_residente', $adulto->cod_residente)
            ->where(function ($query) use ($id): void {
                $query->where('cod_residente_contacto', $id)->orWhere('cod_contacto', $id);
            })
            ->firstOrFail();
    }

    private function volver(AdultoMayor $adulto): RedirectResponse
    {
        return new RedirectResponse(route('admin.adultos-mayores.show', [
            'adulto_mayor' => $adulto->cod_residente,
            'tab' => 'familiares',
        ]));
    }
}
