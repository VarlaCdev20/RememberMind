<?php

namespace App\Livewire\Admin\Voluntariado;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class VoluntariosPanel extends Component
{
    use WithPagination;

    public string $search = '';
    public string $estadoFiltro = '';
    public string $areaFiltro = '';
    public string $tipoFiltro = '';
    public string $disponibilidadFiltro = '';

    public bool $mostrarFormulario = false;
    public bool $isEdit = false;
    public ?int $voluntarioId = null;
    public ?string $codUsu = null;

    public bool $mostrarPerfil = false;
    public ?array $perfil = null;

    public string $nombres = '';
    public string $ap_paterno = '';
    public string $ap_materno = '';
    public string $numero_documento = '';
    public string $expedido = '';
    public string $genero = '';
    public string $fecha_nacimiento = '';
    public string $telefono = '';
    public string $correo = '';
    public string $direccion = '';
    public string $fecha_ing = '';
    public string $area_apoyo = 'Actividades recreativas';
    public string $disponibilidad_inicial = '';
    public string $estado = 'ACTIVO';
    public string $observaciones = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'estadoFiltro' => ['except' => ''],
        'areaFiltro' => ['except' => ''],
        'tipoFiltro' => ['except' => ''],
        'disponibilidadFiltro' => ['except' => ''],
    ];

    public function mount(): void
    {
        $this->fecha_ing = today()->format('Y-m-d');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingEstadoFiltro(): void
    {
        $this->resetPage();
    }

    public function updatingAreaFiltro(): void
    {
        $this->resetPage();
    }

    public function updatingTipoFiltro(): void
    {
        $this->resetPage();
    }

    public function updatingDisponibilidadFiltro(): void
    {
        $this->resetPage();
    }

    public function abrirCrear(): void
    {
        abort_unless(auth()->user()->can('voluntarios.crear'), 403);

        $this->resetForm();
        $this->isEdit = false;
        $this->mostrarFormulario = true;
        $this->mostrarPerfil = false;
    }

    public function editar(int $codVol): void
    {
        abort_unless(auth()->user()->can('voluntarios.editar'), 403);

        $voluntario = $this->voluntarioDetalleQuery()
            ->where('v.cod_vol', $codVol)
            ->first();

        if (! $voluntario) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Voluntario no encontrado',
                'text' => 'No se pudo cargar el registro solicitado.',
            ]);
            return;
        }

        $this->resetValidation();
        $this->voluntarioId = (int) $voluntario->cod_vol;
        $this->codUsu = $voluntario->cod_usu;
        $this->nombres = (string) $voluntario->nombres;
        $this->ap_paterno = (string) $voluntario->ap_paterno;
        $this->ap_materno = (string) $voluntario->ap_materno;
        $this->numero_documento = (string) $voluntario->numero_documento;
        $this->expedido = (string) $voluntario->expedido;
        $this->genero = (string) $voluntario->genero;
        $this->fecha_nacimiento = $voluntario->fecha_nacimiento ? Carbon::parse($voluntario->fecha_nacimiento)->format('Y-m-d') : '';
        $this->telefono = (string) $voluntario->telefono;
        $this->correo = (string) $voluntario->correo;
        $this->direccion = (string) ($voluntario->direccion ?: $voluntario->calle);
        $this->fecha_ing = $voluntario->fecha_ing ? Carbon::parse($voluntario->fecha_ing)->format('Y-m-d') : today()->format('Y-m-d');
        $this->area_apoyo = (string) $voluntario->area_apoyo;
        $this->disponibilidad_inicial = (string) $voluntario->disponibilidad_inicial;
        $this->estado = (string) $voluntario->estado;
        $this->observaciones = (string) $voluntario->observaciones;
        $this->isEdit = true;
        $this->mostrarFormulario = true;
        $this->mostrarPerfil = false;
    }

    public function cerrarFormulario(): void
    {
        $this->mostrarFormulario = false;
        $this->resetForm();
    }

    public function guardar(): void
    {
        $permiso = $this->isEdit ? 'voluntarios.editar' : 'voluntarios.crear';
        abort_unless(auth()->user()->can($permiso), 403);

        $validated = $this->validate($this->rules(), $this->messages());

        try {
            DB::transaction(function () use ($validated) {
                if ($this->isEdit) {
                    $this->actualizarVoluntario($validated);
                    return;
                }

                $this->crearVoluntario($validated);
            });
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'No se pudo guardar',
                'text' => 'Revisa los datos e intenta nuevamente.',
            ]);
            return;
        }

        $this->mostrarFormulario = false;
        $this->resetForm();
        $this->resetPage();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => $this->isEdit ? 'Voluntario actualizado' : 'Voluntario registrado',
            'text' => 'La información quedó guardada sin eliminar historial institucional.',
        ]);
    }

    public function cambiarEstado(int $codVol, string $estado): void
    {
        abort_unless(auth()->user()->can('voluntarios.cambiar_estado'), 403);

        $estado = strtoupper($estado);
        if (! in_array($estado, ['ACTIVO', 'INACTIVO', 'SUSPENDIDO', 'RETIRADO'], true)) {
            return;
        }

        $voluntario = DB::table('voluntarios')->where('cod_vol', $codVol)->first();
        if (! $voluntario) {
            return;
        }

        DB::transaction(function () use ($voluntario, $estado) {
            $volData = [
                'estado' => $estado,
                'updated_at' => now(),
            ];

            if ($estado === 'ACTIVO') {
                $volData['archivado_en'] = null;
                $volData['motivo_archivado'] = null;
            }

            DB::table('voluntarios')
                ->where('cod_vol', $voluntario->cod_vol)
                ->update($volData);

            DB::table('users')
                ->where('cod_usu', $voluntario->cod_usu)
                ->update([
                    'estado' => $estado === 'ACTIVO' ? 'ACTIVO' : 'INACTIVO',
                    'updated_at' => now(),
                ]);
        });

        $this->recargarPerfilSiCorresponde($codVol);

        $this->dispatch('swal', [
            'icon' => $estado === 'ACTIVO' ? 'success' : 'warning',
            'title' => $estado === 'ACTIVO' ? 'Voluntario reactivado' : 'Estado actualizado',
            'text' => 'El voluntario no fue eliminado. Su historial se conserva.',
        ]);
    }

    public function archivar(int $codVol): void
    {
        abort_unless(auth()->user()->can('voluntarios.cambiar_estado'), 403);

        $voluntario = DB::table('voluntarios')->where('cod_vol', $codVol)->first();
        if (! $voluntario) {
            return;
        }

        DB::transaction(function () use ($voluntario) {
            DB::table('voluntarios')
                ->where('cod_vol', $voluntario->cod_vol)
                ->update([
                    'estado' => 'RETIRADO',
                    'archivado_en' => now(),
                    'motivo_archivado' => 'Archivado desde gestión de voluntariado.',
                    'updated_at' => now(),
                ]);

            DB::table('users')
                ->where('cod_usu', $voluntario->cod_usu)
                ->update([
                    'estado' => 'INACTIVO',
                    'updated_at' => now(),
                ]);
        });

        $this->recargarPerfilSiCorresponde($codVol);

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Voluntario archivado',
            'text' => 'Se conservó su historial de disponibilidad, asignaciones y asistencia.',
        ]);
    }

    public function verPerfil(int $codVol): void
    {
        $voluntario = $this->voluntarioDetalleQuery()
            ->where('v.cod_vol', $codVol)
            ->first();

        if (! $voluntario) {
            return;
        }

        $this->perfil = [
            'voluntario' => $voluntario,
            'disponibilidades' => $this->disponibilidades($codVol),
            'asignaciones' => $this->asignacionesActivas($codVol),
            'asistencias' => $this->asistenciasRecientes($codVol),
            'stats' => [
                'disponibilidades' => $this->conteo('disponibilidad_voluntarios', $codVol),
                'asignaciones_activas' => $this->conteoAsignacionesActivas($codVol),
                'asistencias' => $this->conteo('asistencia_voluntarios', $codVol),
            ],
            'links' => $this->linksSubmodulos($codVol),
        ];

        $this->mostrarPerfil = true;
        $this->mostrarFormulario = false;
    }

    public function cerrarPerfil(): void
    {
        $this->mostrarPerfil = false;
        $this->perfil = null;
    }

    public function limpiarFiltros(): void
    {
        $this->reset(['search', 'estadoFiltro', 'areaFiltro', 'tipoFiltro', 'disponibilidadFiltro']);
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.admin.voluntariado.voluntarios-panel', [
            'voluntarios' => $this->voluntariosQuery()->paginate(10),
            'metricas' => $this->metricas(),
            'areas' => $this->areasDisponibles(),
            'estados' => $this->estados(),
            'areasSugeridas' => $this->areasSugeridas(),
            'linksCabecera' => [
                'resumen' => route('admin.voluntariado.index'),
                'disponibilidad' => route('admin.voluntariado.disponibilidad.index'),
            ],
        ]);
    }

    private function rules(): array
    {
        return [
            'nombres' => ['required', 'string', 'max:100'],
            'ap_paterno' => ['nullable', 'required_without:ap_materno', 'string', 'max:80'],
            'ap_materno' => ['nullable', 'required_without:ap_paterno', 'string', 'max:80'],
            'numero_documento' => [
                'nullable',
                'string',
                'max:30',
                Rule::unique('users', 'numero_documento')->ignore($this->codUsu, 'cod_usu'),
            ],
            'expedido' => ['nullable', 'string', 'max:10'],
            'genero' => ['nullable', 'string', 'max:20'],
            'fecha_nacimiento' => ['nullable', 'date', 'before_or_equal:today'],
            'telefono' => ['nullable', 'regex:/^[0-9+()\\s-]{6,20}$/'],
            'correo' => [
                'required',
                'email',
                'max:120',
                Rule::unique('users', 'correo')->ignore($this->codUsu, 'cod_usu'),
            ],
            'direccion' => ['nullable', 'string', 'max:255'],
            'fecha_ing' => ['required', 'date', 'before_or_equal:today'],
            'area_apoyo' => ['required', 'string', 'max:100'],
            'disponibilidad_inicial' => ['nullable', 'string', 'max:150'],
            'estado' => ['required', Rule::in(array_keys($this->estados()))],
            'observaciones' => ['nullable', 'string', 'max:1500'],
        ];
    }

    private function messages(): array
    {
        return [
            'nombres.required' => 'Registra los nombres del voluntario.',
            'ap_paterno.required_without' => 'Registra al menos un apellido.',
            'ap_materno.required_without' => 'Registra al menos un apellido.',
            'numero_documento.unique' => 'Ya existe un usuario con esta cédula.',
            'fecha_nacimiento.before_or_equal' => 'La fecha de nacimiento no puede ser futura.',
            'telefono.regex' => 'Registra un celular válido.',
            'correo.required' => 'El correo es obligatorio para crear el usuario vinculado.',
            'correo.email' => 'Registra un correo válido.',
            'correo.unique' => 'Ya existe un usuario con este correo.',
            'fecha_ing.required' => 'Registra la fecha de ingreso.',
            'fecha_ing.before_or_equal' => 'La fecha de ingreso no puede ser futura.',
            'area_apoyo.required' => 'Selecciona el área de apoyo.',
            'estado.required' => 'Selecciona el estado del voluntario.',
        ];
    }

    private function crearVoluntario(array $validated): void
    {
        $passwordTemporal = Str::password(12);

        $usuario = User::create([
            'nombres' => $validated['nombres'],
            'ap_paterno' => $validated['ap_paterno'] ?: null,
            'ap_materno' => $validated['ap_materno'] ?: null,
            'pais_documento' => 'Bolivia',
            'tipo_documento' => 'CI',
            'numero_documento' => $validated['numero_documento'] ?: null,
            'expedido' => $validated['expedido'] ?: null,
            'correo' => Str::lower($validated['correo']),
            'password' => Hash::make($passwordTemporal),
            'telefono' => $validated['telefono'] ?: null,
            'genero' => $validated['genero'] ?: null,
            'fecha_nacimiento' => $validated['fecha_nacimiento'] ?: null,
            'estado' => $validated['estado'] === 'ACTIVO' ? 'ACTIVO' : 'INACTIVO',
            'acceso_sistema' => 'HABILITADO',
            'direccion' => $validated['direccion'] ?: null,
            'tipo_vinculacion' => 'VOLUNTARIADO',
            'cod_area' => $this->areaInstitucionalVoluntariado(),
            'debe_cambiar_password' => true,
            'observaciones' => $validated['observaciones'] ?: null,
        ]);

        if (Role::where('name', 'voluntario')->exists()) {
            $usuario->assignRole('voluntario');
        }

        DB::table('voluntarios')->insert([
            'fecha_ing' => $validated['fecha_ing'],
            'area_apoyo' => $validated['area_apoyo'],
            'estado' => $validated['estado'],
            'observaciones' => $validated['observaciones'] ?: null,
            'cod_usu' => $usuario->cod_usu,
            'disponibilidad_inicial' => $validated['disponibilidad_inicial'] ?: null,
            'area_apoyo_preferente' => $validated['area_apoyo'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function actualizarVoluntario(array $validated): void
    {
        DB::table('users')
            ->where('cod_usu', $this->codUsu)
            ->update([
                'nombres' => $validated['nombres'],
                'ap_paterno' => $validated['ap_paterno'] ?: null,
                'ap_materno' => $validated['ap_materno'] ?: null,
                'numero_documento' => $validated['numero_documento'] ?: null,
                'expedido' => $validated['expedido'] ?: null,
                'correo' => Str::lower($validated['correo']),
                'telefono' => $validated['telefono'] ?: null,
                'genero' => $validated['genero'] ?: null,
                'fecha_nacimiento' => $validated['fecha_nacimiento'] ?: null,
                'estado' => $validated['estado'] === 'ACTIVO' ? 'ACTIVO' : 'INACTIVO',
                'direccion' => $validated['direccion'] ?: null,
                'tipo_vinculacion' => 'VOLUNTARIADO',
                'observaciones' => $validated['observaciones'] ?: null,
                'updated_at' => now(),
            ]);

        DB::table('voluntarios')
            ->where('cod_vol', $this->voluntarioId)
            ->update([
                'fecha_ing' => $validated['fecha_ing'],
                'area_apoyo' => $validated['area_apoyo'],
                'estado' => $validated['estado'],
                'observaciones' => $validated['observaciones'] ?: null,
                'disponibilidad_inicial' => $validated['disponibilidad_inicial'] ?: null,
                'area_apoyo_preferente' => $validated['area_apoyo'],
                'archivado_en' => $validated['estado'] === 'ACTIVO' ? null : DB::raw('archivado_en'),
                'updated_at' => now(),
            ]);
    }

    private function voluntariosQuery(): Builder
    {
        $query = $this->voluntarioBaseQuery()
            ->select([
                'v.cod_vol',
                'v.cod_usu',
                'v.fecha_ing',
                'v.area_apoyo',
                'v.estado',
                'v.observaciones',
                'v.archivado_en',
                'v.motivo_archivado',
                'v.disponibilidad_inicial',
                'u.nombres',
                'u.ap_paterno',
                'u.ap_materno',
                'u.numero_documento',
                'u.expedido',
                'u.telefono',
                'u.correo',
                'u.tipo_vinculacion',
                'u.fecha_nacimiento',
                DB::raw('(select count(*) from disponibilidad_voluntarios dv where dv.cod_vol = v.cod_vol) as disponibilidad_count'),
                DB::raw("(select count(*) from asignacion_voluntarios av where av.cod_vol = v.cod_vol and av.fecha_asig <= CURRENT_DATE and (av.fecha_fin is null or av.fecha_fin >= CURRENT_DATE) and upper(coalesce(av.estado, '')) not in ('INACTIVO','INACTIVA','FINALIZADO','FINALIZADA','CANCELADO','CANCELADA','ANULADO','ANULADA')) as asignaciones_activas_count"),
                DB::raw('(select count(*) from asistencia_voluntarios asi where asi.cod_vol = v.cod_vol) as asistencias_count'),
                DB::raw('(select max(asi.fecha) from asistencia_voluntarios asi where asi.cod_vol = v.cod_vol) as ultima_asistencia'),
            ]);

        if ($this->search !== '') {
            $term = '%' . trim($this->search) . '%';
            $query->where(function (Builder $q) use ($term) {
                $q->where('u.nombres', 'ilike', $term)
                    ->orWhere('u.ap_paterno', 'ilike', $term)
                    ->orWhere('u.ap_materno', 'ilike', $term)
                    ->orWhere('u.numero_documento', 'ilike', $term)
                    ->orWhere('u.telefono', 'ilike', $term)
                    ->orWhere('u.correo', 'ilike', $term);
            });
        }

        if ($this->estadoFiltro !== '') {
            if ($this->estadoFiltro === 'ARCHIVADO') {
                $query->whereNotNull('v.archivado_en');
            } else {
                $query->where('v.estado', $this->estadoFiltro)
                    ->whereNull('v.archivado_en');
            }
        }

        if ($this->areaFiltro !== '') {
            $query->where('v.area_apoyo', $this->areaFiltro);
        }

        if ($this->tipoFiltro !== '') {
            $query->where('u.tipo_vinculacion', $this->tipoFiltro);
        }

        if ($this->disponibilidadFiltro === 'si') {
            $query->whereExists(function (Builder $q) {
                $q->select('dv.cod_vol')
                    ->from('disponibilidad_voluntarios as dv')
                    ->whereColumn('dv.cod_vol', 'v.cod_vol');
            });
        } elseif ($this->disponibilidadFiltro === 'no') {
            $query->whereNotExists(function (Builder $q) {
                $q->select('dv.cod_vol')
                    ->from('disponibilidad_voluntarios as dv')
                    ->whereColumn('dv.cod_vol', 'v.cod_vol');
            });
        }

        return $query
            ->orderByRaw('case when v.archivado_en is null then 0 else 1 end')
            ->orderByRaw("case when v.estado = 'ACTIVO' then 0 when v.estado = 'SUSPENDIDO' then 1 when v.estado = 'INACTIVO' then 2 else 3 end")
            ->orderBy('u.ap_paterno')
            ->orderBy('u.nombres');
    }

    private function voluntarioBaseQuery(): Builder
    {
        return DB::table('voluntarios as v')
            ->join('users as u', 'v.cod_usu', '=', 'u.cod_usu');
    }

    private function voluntarioDetalleQuery(): Builder
    {
        return $this->voluntarioBaseQuery()
            ->select([
                'v.cod_vol',
                'v.cod_usu',
                'v.fecha_ing',
                'v.area_apoyo',
                'v.estado',
                'v.observaciones',
                'v.archivado_en',
                'v.motivo_archivado',
                'v.disponibilidad_inicial',
                'v.area_apoyo_preferente',
                'u.nombres',
                'u.ap_paterno',
                'u.ap_materno',
                'u.numero_documento',
                'u.expedido',
                'u.genero',
                'u.fecha_nacimiento',
                'u.telefono',
                'u.correo',
                'u.direccion',
                'u.calle',
                'u.tipo_vinculacion',
            ]);
    }

    private function metricas(): array
    {
        $totalActuales = DB::table('voluntarios')->whereNull('archivado_en')->count();
        $activos = DB::table('voluntarios')->whereNull('archivado_en')->where('estado', 'ACTIVO')->count();
        $inactivos = DB::table('voluntarios')->whereNull('archivado_en')->where('estado', 'INACTIVO')->count();
        $archivados = DB::table('voluntarios')->whereNotNull('archivado_en')->count();
        $sinDisponibilidad = DB::table('voluntarios as v')
            ->whereNull('v.archivado_en')
            ->whereNotExists(function (Builder $q) {
                $q->select('dv.cod_vol')
                    ->from('disponibilidad_voluntarios as dv')
                    ->whereColumn('dv.cod_vol', 'v.cod_vol');
            })
            ->count();
        $conAsignaciones = DB::table('voluntarios as v')
            ->whereNull('v.archivado_en')
            ->whereExists(function (Builder $q) {
                $q->select('av.cod_vol')
                    ->from('asignacion_voluntarios as av')
                    ->whereColumn('av.cod_vol', 'v.cod_vol')
                    ->whereDate('av.fecha_asig', '<=', today())
                    ->where(function (Builder $fin) {
                        $fin->whereNull('av.fecha_fin')->orWhereDate('av.fecha_fin', '>=', today());
                    });
            })
            ->count();
        $conAsistencia = DB::table('voluntarios as v')
            ->whereNull('v.archivado_en')
            ->whereExists(function (Builder $q) {
                $q->select('a.cod_vol')
                    ->from('asistencia_voluntarios as a')
                    ->whereColumn('a.cod_vol', 'v.cod_vol');
            })
            ->count();
        $recientes = DB::table('voluntarios')
            ->whereNull('archivado_en')
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        return [
            ['label' => 'Total voluntarios', 'valor' => $totalActuales, 'subtitulo' => 'Registros no archivados', 'icono' => 'ph-users-three', 'tono' => 'azul'],
            ['label' => 'Voluntarios activos', 'valor' => $activos, 'subtitulo' => 'Disponibles para el flujo', 'icono' => 'ph-user-check', 'tono' => 'verde'],
            ['label' => 'Voluntarios inactivos', 'valor' => $inactivos, 'subtitulo' => 'Sin participación activa', 'icono' => 'ph-user-minus', 'tono' => 'neutro'],
            ['label' => 'Sin disponibilidad', 'valor' => $sinDisponibilidad, 'subtitulo' => 'Pendientes de horario', 'icono' => 'ph-calendar-x', 'tono' => 'terracota'],
            ['label' => 'Con asignaciones activas', 'valor' => $conAsignaciones, 'subtitulo' => 'Apoyo vigente', 'icono' => 'ph-handshake', 'tono' => 'azul'],
            ['label' => 'Con asistencia registrada', 'valor' => $conAsistencia, 'subtitulo' => 'Participación evidenciada', 'icono' => 'ph-clipboard-text', 'tono' => 'verde'],
            ['label' => 'Voluntarios recientes', 'valor' => $recientes, 'subtitulo' => 'Registrados últimos 30 días', 'icono' => 'ph-sparkle', 'tono' => 'dorado'],
            ['label' => 'Voluntarios archivados', 'valor' => $archivados, 'subtitulo' => 'Historial conservado', 'icono' => 'ph-archive-box', 'tono' => 'neutro'],
        ];
    }

    private function disponibilidades(int $codVol)
    {
        return DB::table('disponibilidad_voluntarios')
            ->where('cod_vol', $codVol)
            ->orderByRaw("case dia_semana when 'Lunes' then 1 when 'Martes' then 2 when 'Miércoles' then 3 when 'Miercoles' then 3 when 'Jueves' then 4 when 'Viernes' then 5 when 'Sábado' then 6 when 'Sabado' then 6 when 'Domingo' then 7 else 8 end")
            ->orderBy('hora_inicio')
            ->get();
    }

    private function asignacionesActivas(int $codVol)
    {
        return DB::table('asignacion_voluntarios as av')
            ->leftJoin('adulto_mayor as am', 'av.cod_am', '=', 'am.cod_am')
            ->select('av.*', 'am.nombres', 'am.ap_paterno', 'am.ap_materno')
            ->where('av.cod_vol', $codVol)
            ->whereDate('av.fecha_asig', '<=', today())
            ->where(function (Builder $query) {
                $query->whereNull('av.fecha_fin')
                    ->orWhereDate('av.fecha_fin', '>=', today());
            })
            ->orderByDesc('av.fecha_asig')
            ->limit(5)
            ->get();
    }

    private function asistenciasRecientes(int $codVol)
    {
        return DB::table('asistencia_voluntarios')
            ->where('cod_vol', $codVol)
            ->orderByDesc('fecha')
            ->limit(5)
            ->get();
    }

    private function conteo(string $tabla, int $codVol): int
    {
        return Schema::hasTable($tabla)
            ? DB::table($tabla)->where('cod_vol', $codVol)->count()
            : 0;
    }

    private function conteoAsignacionesActivas(int $codVol): int
    {
        return DB::table('asignacion_voluntarios')
            ->where('cod_vol', $codVol)
            ->whereDate('fecha_asig', '<=', today())
            ->where(function (Builder $query) {
                $query->whereNull('fecha_fin')->orWhereDate('fecha_fin', '>=', today());
            })
            ->count();
    }

    private function recargarPerfilSiCorresponde(int $codVol): void
    {
        if ($this->mostrarPerfil && $this->perfil && (int) $this->perfil['voluntario']->cod_vol === $codVol) {
            $this->verPerfil($codVol);
        }
    }

    private function resetForm(): void
    {
        $this->resetValidation();
        $this->voluntarioId = null;
        $this->codUsu = null;
        $this->nombres = '';
        $this->ap_paterno = '';
        $this->ap_materno = '';
        $this->numero_documento = '';
        $this->expedido = '';
        $this->genero = '';
        $this->fecha_nacimiento = '';
        $this->telefono = '';
        $this->correo = '';
        $this->direccion = '';
        $this->fecha_ing = today()->format('Y-m-d');
        $this->area_apoyo = 'Actividades recreativas';
        $this->disponibilidad_inicial = '';
        $this->estado = 'ACTIVO';
        $this->observaciones = '';
    }

    private function areasDisponibles(): array
    {
        return DB::table('voluntarios')
            ->whereNotNull('area_apoyo')
            ->distinct()
            ->orderBy('area_apoyo')
            ->pluck('area_apoyo')
            ->filter()
            ->values()
            ->all();
    }

    private function areasSugeridas(): array
    {
        return [
            'Actividades recreativas',
            'Actividades cognitivas',
            'Acompañamiento social',
            'Apoyo administrativo',
            'Apoyo logístico',
            'Apoyo en alimentación',
            'Apoyo en eventos',
            'Otro',
        ];
    }

    private function estados(): array
    {
        return [
            'ACTIVO' => 'Activo',
            'INACTIVO' => 'Inactivo',
            'SUSPENDIDO' => 'Suspendido',
            'RETIRADO' => 'Retirado',
        ];
    }

    private function linksSubmodulos(int $codVol): array
    {
        return [
            'disponibilidad' => route('admin.voluntariado.disponibilidad.index', ['voluntario' => $codVol]),
            'asignaciones' => route('admin.voluntariado.asignaciones.index', ['voluntario' => $codVol]),
            'asistencia' => route('admin.voluntariado.asistencia.index', ['voluntario' => $codVol]),
        ];
    }

    private function areaInstitucionalVoluntariado(): ?string
    {
        if (! Schema::hasTable('areas_institucionales')) {
            return null;
        }

        $codigo = DB::table('areas_institucionales')->where('cod_area', 'ARE_0008')->value('cod_area');
        if ($codigo) {
            return $codigo;
        }

        return DB::table('areas_institucionales')
            ->where('nombre', 'ilike', '%volunt%')
            ->value('cod_area');
    }
}
