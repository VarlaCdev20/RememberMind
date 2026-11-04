<?php

declare(strict_types=1);

namespace App\Modulos\Valoraciones\Presentacion\Livewire\Paginas;

use App\Models\Residente;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

final class ActividadPsicologicaGeneral extends Component
{
    use WithPagination;

    public string $buscar = '';

    #[Locked]
    public string $seccion;

    public function mount(): void
    {
        $this->seccion = match (request()->route()?->getName()) {
            'admin.psicologia.agenda' => 'agenda',
            'admin.psicologia.seguimiento' => 'seguimiento',
            'admin.psicologia.informes' => 'informes',
            default => abort(404),
        };
    }

    public function updatingBuscar(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $usuario = $this->autorizar();
        $seccion = $this->seccion;

        $permiso = $seccion === 'informes' ? 'valoraciones_psicologicas.ver' : 'atenciones.ver';
        abort_unless($usuario->can($permiso), 403);

        $personal = $usuario->personal;
        $registros = $seccion === 'informes'
            ? DB::table('valoraciones_psicologicas as registro')
            : DB::table('atenciones as registro');

        $registros->join('residentes as residente', 'residente.cod_residente', '=', 'registro.cod_residente')
            ->where('residente.estado', 'ADMITIDO');

        // La actividad general pertenece al profesional; la supervisión puede consultar todo.
        if (! $usuario->hasRole('SUPERADMINISTRADOR')) {
            $registros->where('registro.cod_personal', $personal->getKey());
        }

        if ($seccion === 'informes') {
            $registros->whereNotNull('registro.conclusion');
        } else {
            $registros->whereExists(static function ($query): void {
                $query->selectRaw('1')->from('personal')
                    ->whereColumn('personal.cod_personal', 'registro.cod_personal')
                    ->where('personal.profesion', 'PSICOLOGIA');
            });
        }

        if ($seccion === 'agenda') {
            $registros->whereIn('registro.estado', ['PROGRAMADA', 'PENDIENTE'])
                ->where('registro.fecha_hora', '>=', now());
        } elseif ($seccion === 'seguimiento') {
            $registros->where('registro.fecha_hora', '<=', now())
                ->where('registro.estado', 'FINALIZADA');
        }

        $termino = mb_strtolower(trim(mb_substr($this->buscar, 0, 100)));
        if ($termino !== '') {
            $registros->where(static function ($query) use ($termino): void {
                foreach (['nombres', 'apellido_paterno', 'apellido_materno'] as $campo) {
                    $query->orWhereRaw('LOWER(residente.'.$campo.') LIKE ?', ['%'.$termino.'%']);
                }
            });
        }

        $columnas = [
            'residente.cod_residente', 'residente.nombres', 'residente.apellido_paterno',
            'residente.apellido_materno', 'registro.fecha_hora', 'registro.estado',
        ];
        if ($seccion === 'informes') {
            $columnas[] = 'registro.cod_valoracion_psicologica as clave_registro';
            $columnas[] = 'registro.conclusion';
            $columnas[] = 'registro.recomendacion';
        } else {
            $columnas[] = 'registro.cod_atencion as clave_registro';
            $columnas[] = 'registro.tipo_atencion';
            $columnas[] = 'registro.motivo';
        }

        $registros = $registros->orderBy('registro.fecha_hora', $seccion === 'agenda' ? 'asc' : 'desc')
            ->orderBy('registro.'.($seccion === 'informes' ? 'cod_valoracion_psicologica' : 'cod_atencion'))
            ->paginate(12, $columnas);

        return view('valoraciones::livewire.valoraciones.actividad-psicologica-general', compact('registros', 'seccion'))
            ->layout('layouts.sistema');
    }

    private function autorizar(): User
    {
        $usuario = auth()->user() ? User::query()->find(auth()->id()) : null;
        abort_unless($usuario?->estado === 'ACTIVO'
            && $usuario->hasAnyRole(['PSICOLOGO/A', 'SUPERADMINISTRADOR'])
            && ! $usuario->hasRole('FAMILIAR')
            && Gate::forUser($usuario)->allows('viewAny', Residente::class)
            && ($usuario->hasRole('SUPERADMINISTRADOR')
                || $usuario->personal()->where('estado', 'ACTIVO')->where('profesion', 'PSICOLOGIA')->exists()), 403);

        return $usuario;
    }
}
