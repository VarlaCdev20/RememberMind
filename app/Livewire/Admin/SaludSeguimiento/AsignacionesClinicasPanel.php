<?php

namespace App\Livewire\Admin\SaludSeguimiento;

use App\Models\AdultoMayor;
use App\Models\AsignacionSaludAdulto;
use App\Models\PersonalSalud;
use App\Support\ClinicalAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class AsignacionesClinicasPanel extends Component
{
    use WithPagination;

    public string $buscar = '';
    public string $filtroAdulto = '';
    public string $filtroPersonal = '';
    public string $filtroEstado = '';
    public string $filtroTipo = '';

    public bool $mostrarFormulario = false;
    public string $cod_am = '';
    public string $cod_per_sal = '';
    public string $tipo_asignacion = 'principal';
    public string $fecha_inicio = '';
    public string $fecha_fin = '';
    public string $motivo = '';

    protected $queryString = [
        'buscar' => ['except' => ''],
        'filtroAdulto' => ['except' => ''],
        'filtroPersonal' => ['except' => ''],
        'filtroEstado' => ['except' => ''],
        'filtroTipo' => ['except' => ''],
    ];

    public function mount(): void
    {
        abort_if(! $this->puedeVerPanel(), 403);
        $this->fecha_inicio = today()->format('Y-m-d');
    }

    public function updated($property): void
    {
        if (in_array($property, ['buscar', 'filtroAdulto', 'filtroPersonal', 'filtroEstado', 'filtroTipo'], true)) {
            $this->resetPage();
        }
    }

    public function abrirFormulario(): void
    {
        if (! $this->puedeCrear()) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acceso denegado',
                'text' => 'No tienes permiso para crear asignaciones clinicas.',
            ]);
            return;
        }

        $this->resetForm();
        $this->mostrarFormulario = true;
    }

    public function cerrarFormulario(): void
    {
        $this->mostrarFormulario = false;
        $this->resetForm();
    }

    public function guardarAsignacion(): void
    {
        if (! $this->puedeCrear()) {
            abort(403);
        }

        $this->validate($this->rules(), $this->messages());

        $duplicada = AsignacionSaludAdulto::query()
            ->activa()
            ->where('cod_am', $this->cod_am)
            ->where('cod_per_sal', $this->cod_per_sal)
            ->where('tipo_asignacion', $this->tipo_asignacion)
            ->exists();

        if ($duplicada) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Asignacion activa existente',
                'text' => 'Ya existe una asignacion activa del mismo tipo para este adulto mayor y personal de salud.',
            ]);
            return;
        }

        DB::transaction(function () {
            AsignacionSaludAdulto::create([
                'cod_am' => $this->cod_am,
                'cod_per_sal' => (int) $this->cod_per_sal,
                'asignado_por' => Auth::user()->cod_usu,
                'tipo_asignacion' => $this->tipo_asignacion,
                'fecha_inicio' => $this->fecha_inicio,
                'fecha_fin' => $this->fecha_fin ?: null,
                'estado' => AsignacionSaludAdulto::ESTADO_ACTIVA,
                'motivo' => $this->motivo ?: null,
            ]);
        });

        $this->mostrarFormulario = false;
        $this->resetForm();
        $this->resetPage();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Asignacion creada',
            'text' => 'El personal de salud quedo asignado al adulto mayor seleccionado.',
        ]);
    }

    public function finalizarAsignacion(int $id): void
    {
        if (! $this->puedeFinalizar()) {
            abort(403);
        }

        $asignacion = AsignacionSaludAdulto::findOrFail($id);

        if ($asignacion->estado !== AsignacionSaludAdulto::ESTADO_ACTIVA) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Asignacion no activa',
                'text' => 'Solo las asignaciones activas pueden finalizarse.',
            ]);
            return;
        }

        $asignacion->update([
            'estado' => AsignacionSaludAdulto::ESTADO_FINALIZADA,
            'fecha_fin' => $asignacion->fecha_fin ?: today(),
            'motivo' => $asignacion->motivo ?: 'Finalizada desde el panel de asignaciones clinicas.',
        ]);

        activity('Asignaciones clinicas')
            ->causedBy(Auth::user())
            ->performedOn($asignacion)
            ->event('finalizada')
            ->log("Se finalizo la asignacion clinica #{$asignacion->id} del adulto mayor {$asignacion->cod_am}.");

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Asignacion finalizada',
            'text' => 'La relacion clinica quedo cerrada sin eliminar datos.',
        ]);
    }

    public function suspenderAsignacion(int $id): void
    {
        if (! $this->puedeSuspender()) {
            abort(403);
        }

        $asignacion = AsignacionSaludAdulto::findOrFail($id);

        if ($asignacion->estado !== AsignacionSaludAdulto::ESTADO_ACTIVA) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Asignacion no activa',
                'text' => 'Solo las asignaciones activas pueden suspenderse.',
            ]);
            return;
        }

        $asignacion->update([
            'estado' => AsignacionSaludAdulto::ESTADO_SUSPENDIDA,
            'fecha_fin' => $asignacion->fecha_fin ?: today(),
            'motivo' => $asignacion->motivo ?: 'Suspendida desde el panel de asignaciones clinicas.',
        ]);

        activity('Asignaciones clinicas')
            ->causedBy(Auth::user())
            ->performedOn($asignacion)
            ->event('suspendida')
            ->log("Se suspendio la asignacion clinica #{$asignacion->id} del adulto mayor {$asignacion->cod_am}.");

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Asignacion suspendida',
            'text' => 'La asignacion quedo suspendida y conservada en el historial.',
        ]);
    }

    public function limpiarFiltros(): void
    {
        $this->reset(['buscar', 'filtroAdulto', 'filtroPersonal', 'filtroEstado', 'filtroTipo']);
        $this->resetPage();
    }

    public function render()
    {
        $asignaciones = $this->asignacionesQuery()
            ->orderByRaw("CASE WHEN estado = 'activa' THEN 0 WHEN estado = 'suspendida' THEN 1 ELSE 2 END")
            ->orderByDesc('fecha_inicio')
            ->paginate(10);

        return view('livewire.admin.salud-seguimiento.asignaciones-clinicas-panel', [
            'asignaciones' => $asignaciones,
            'adultosDisponibles' => $this->adultosDisponibles(),
            'personalSaludDisponible' => $this->personalSaludDisponible(),
            'metricas' => $this->metricas(),
            'puedeGestionar' => $this->puedeGestionar(),
            'puedeCrear' => $this->puedeCrear(),
            'esVistaPersonal' => ClinicalAccess::isHealthStaff(Auth::user()) && ! ClinicalAccess::userCanManageAll(Auth::user()),
            'tiposAsignacion' => AsignacionSaludAdulto::TIPOS,
            'estadosAsignacion' => AsignacionSaludAdulto::ESTADOS,
        ])->layout('layouts.sistema');
    }

    protected function rules(): array
    {
        return [
            'cod_am' => ['required', 'string', 'exists:adulto_mayor,cod_am'],
            'cod_per_sal' => ['required', 'integer', 'exists:personal_salud,cod_per_sal'],
            'tipo_asignacion' => ['required', 'in:' . implode(',', AsignacionSaludAdulto::TIPOS)],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'motivo' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function messages(): array
    {
        return [
            'cod_am.required' => 'Debe seleccionar un adulto mayor.',
            'cod_am.exists' => 'El adulto mayor seleccionado no existe.',
            'cod_per_sal.required' => 'Debe seleccionar un personal de salud.',
            'cod_per_sal.exists' => 'El personal de salud seleccionado no existe.',
            'tipo_asignacion.required' => 'Debe seleccionar el tipo de asignacion.',
            'tipo_asignacion.in' => 'Seleccione un tipo de asignacion valido.',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_inicio.date' => 'La fecha de inicio no es valida.',
            'fecha_fin.date' => 'La fecha de fin no es valida.',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
            'motivo.max' => 'El motivo no debe superar los 1000 caracteres.',
        ];
    }

    private function asignacionesQuery(): Builder
    {
        $user = Auth::user();

        return AsignacionSaludAdulto::query()
            ->with([
                'adultoMayor.estado',
                'personalSalud.usuario',
                'personalSalud.especialidad',
                'asignador',
            ])
            ->when(ClinicalAccess::isHealthStaff($user) && ! ClinicalAccess::userCanManageAll($user), function (Builder $query) use ($user) {
                $query->where('cod_per_sal', ClinicalAccess::personalSaludId($user))
                    ->where('estado', AsignacionSaludAdulto::ESTADO_ACTIVA);
            })
            ->when($this->filtroAdulto, fn (Builder $query) => $query->where('cod_am', $this->filtroAdulto))
            ->when($this->filtroPersonal && ClinicalAccess::userCanManageAll($user), fn (Builder $query) => $query->where('cod_per_sal', $this->filtroPersonal))
            ->when($this->filtroEstado && ClinicalAccess::userCanManageAll($user), fn (Builder $query) => $query->where('estado', $this->filtroEstado))
            ->when($this->filtroTipo, fn (Builder $query) => $query->where('tipo_asignacion', $this->filtroTipo))
            ->when(trim($this->buscar) !== '', function (Builder $query) {
                $buscar = '%' . trim($this->buscar) . '%';
                $query->where(function (Builder $subQuery) use ($buscar) {
                    $subQuery->where('cod_am', 'ilike', $buscar)
                        ->orWhereHas('adultoMayor', function (Builder $adulto) use ($buscar) {
                            $adulto->where('nombres', 'ilike', $buscar)
                                ->orWhere('ap_paterno', 'ilike', $buscar)
                                ->orWhere('ap_materno', 'ilike', $buscar)
                                ->orWhere('ci', 'like', $buscar);
                        })
                        ->orWhereHas('personalSalud.usuario', function (Builder $usuario) use ($buscar) {
                            $usuario->where('nombres', 'ilike', $buscar)
                                ->orWhere('ap_paterno', 'ilike', $buscar)
                                ->orWhere('ap_materno', 'ilike', $buscar)
                                ->orWhere('correo', 'ilike', $buscar);
                        });
                });
            });
    }

    private function adultosDisponibles()
    {
        return AdultoMayor::query()
            ->visiblesClinicamentePara(Auth::user())
            ->with('estado')
            ->orderBy('ap_paterno')
            ->orderBy('ap_materno')
            ->orderBy('nombres')
            ->get();
    }

    private function personalSaludDisponible()
    {
        $user = Auth::user();

        return PersonalSalud::query()
            ->with(['usuario', 'especialidad'])
            ->when(ClinicalAccess::isHealthStaff($user) && ! ClinicalAccess::userCanManageAll($user), function (Builder $query) use ($user) {
                $query->where('cod_per_sal', ClinicalAccess::personalSaludId($user));
            })
            ->whereHas('usuario', function (Builder $query) {
                $query->whereIn('estado', ['ACTIVO', '1']);
            })
            ->get()
            ->sortBy(fn (PersonalSalud $personal) => $personal->usuario?->name ?? 'Sin usuario')
            ->values();
    }

    private function metricas(): array
    {
        $base = $this->asignacionesQuery();
        $activas = (clone $base)->where('estado', AsignacionSaludAdulto::ESTADO_ACTIVA)->count();

        return [
            'activas' => $activas,
            'finalizadas' => (clone $base)->where('estado', AsignacionSaludAdulto::ESTADO_FINALIZADA)->count(),
            'suspendidas' => (clone $base)->where('estado', AsignacionSaludAdulto::ESTADO_SUSPENDIDA)->count(),
            'adultos' => (clone $base)->where('estado', AsignacionSaludAdulto::ESTADO_ACTIVA)->distinct('cod_am')->count('cod_am'),
            'personal' => (clone $base)->where('estado', AsignacionSaludAdulto::ESTADO_ACTIVA)->distinct('cod_per_sal')->count('cod_per_sal'),
        ];
    }

    private function resetForm(): void
    {
        $this->resetValidation();
        $this->cod_am = '';
        $this->cod_per_sal = '';
        $this->tipo_asignacion = 'principal';
        $this->fecha_inicio = today()->format('Y-m-d');
        $this->fecha_fin = '';
        $this->motivo = '';
    }

    private function puedeVerPanel(): bool
    {
        $user = Auth::user();

        return $user?->can('salud.ver')
            || $user?->can('asignaciones_clinicas.ver')
            || $user?->can('asignaciones_clinicas.mis_pacientes')
            || ClinicalAccess::userCanManageAll($user)
            || ClinicalAccess::isHealthStaff($user);
    }

    private function puedeGestionar(): bool
    {
        $user = Auth::user();

        return ClinicalAccess::userCanManageAll($user)
            || $user?->can('asignaciones_clinicas.gestionar');
    }

    private function puedeCrear(): bool
    {
        $user = Auth::user();

        return $this->puedeGestionar() || $user?->can('asignaciones_clinicas.crear');
    }

    private function puedeFinalizar(): bool
    {
        $user = Auth::user();

        return $this->puedeGestionar() || $user?->can('asignaciones_clinicas.finalizar');
    }

    private function puedeSuspender(): bool
    {
        $user = Auth::user();

        return $this->puedeGestionar() || $user?->can('asignaciones_clinicas.suspender');
    }
}
