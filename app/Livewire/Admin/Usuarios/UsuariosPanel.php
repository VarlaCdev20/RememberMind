<?php

namespace App\Livewire\Admin\Usuarios;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UsuariosPanel extends Component
{
    use WithPagination;

    // ── Filtros ──
    public $search = '';
    public $filtroRol = '';
    public $filtroEstado = '';
    public $filtroGenero = '';

    // ── Modal Formulario Crear/Editar ──
    public bool $mostrarFormulario = false;
    public int $pasoFormulario = 1;
    public ?string $usuarioId = null;
    public $isEdit = false;

    // ── Campos del Usuario ──
    public $cod_usu;
    public $nombres;
    public $ap_paterno;
    public $ap_materno;
    public $fecha_nacimiento;
    public $genero;
    public $pais_documento = 'Bolivia';
    public $tipo_documento = 'CI';
    public $numero_documento;
    public $expedido;
    public $correo;
    public $telefono;
    public $pais_telefono = 'Bolivia';
    public $codigo_telefono = '+591';
    public $rol;
    public $estado = 'ACTIVO';
    public $acceso_sistema = 'HABILITADO';
    public $observaciones;
    public $password;
    public $password_confirmation;

    // ── Campos condicionales (Personal Salud/Admin) ──
    public $fecha_ingreso;
    public $especialidad_salud;
    public $cargo_administrativo;

    // ── Ficha Rápida Flotante (panel lateral derecho) ──
    public bool $mostrarFichaRapida = false;
    public ?string $usuarioFichaId = null;
    public $usuarioFicha = null;

    // ── Vista Completa (modal/panel expandido) ──
    public bool $mostrarVistaCompleta = false;
    public $usuarioVista = null;

    protected $listeners = ['usuario-guardado' => '$refresh'];

    // ══════════════════════════════════════════════
    // VALIDACIÓN
    // ══════════════════════════════════════════════

    public function rules()
    {
        $rules = [
            'nombres' => ['required', 'string', 'max:255'],
            'ap_paterno' => ['required', 'string', 'max:255'],
            'ap_materno' => ['nullable', 'string', 'max:255'],
            'fecha_nacimiento' => ['required', 'date', 'before:today'],
            'genero' => ['required', 'string'],
            'pais_documento' => ['required', 'string'],
            'tipo_documento' => ['required', 'string'],
            'numero_documento' => ['required', 'string', 'max:50'],
            'correo' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'correo')->ignore($this->usuarioId, 'cod_usu')],
            'telefono' => ['required', 'string', 'max:20'],
            'rol' => ['required', 'exists:roles,name'],
            'acceso_sistema' => ['required', 'in:HABILITADO,BLOQUEADO'],
        ];

        if ($this->isEdit) {
            $rules['estado'] = ['required', 'in:ACTIVO,INACTIVO,ARCHIVADO'];
            $rules['password'] = ['nullable', 'string', 'min:8', 'confirmed'];
        }

        if ($this->rol === 'personal_salud') {
            $rules['especialidad_salud'] = ['required', 'exists:especialidades,cod_esp'];
        }

        if ($this->rol === 'personal_admin') {
            $rules['cargo_administrativo'] = ['required', 'exists:cargos_administrativos,cod_cargo_admin'];
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'nombres.required' => 'El nombre es obligatorio.',
            'fecha_nacimiento.required' => 'La fecha de nacimiento es obligatoria.',
            'genero.required' => 'Seleccione el sexo.',
            'numero_documento.required' => 'El número de documento es obligatorio.',
            'correo.required' => 'El correo es obligatorio.',
            'correo.email' => 'El correo debe ser válido.',
            'correo.unique' => 'Este correo ya está registrado.',
            'telefono.required' => 'El teléfono es obligatorio.',
            'rol.required' => 'Debe seleccionar un rol.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'especialidad_salud.required' => 'Seleccione una especialidad.',
            'cargo_administrativo.required' => 'Seleccione un cargo administrativo.',
        ];
    }

    // ══════════════════════════════════════════════
    // FORMULARIO: CREAR / EDITAR
    // ══════════════════════════════════════════════

    public function resetFormulario()
    {
        $this->reset([
            'cod_usu', 'nombres', 'ap_paterno', 'ap_materno', 'fecha_nacimiento', 'genero',
            'pais_documento', 'tipo_documento', 'numero_documento', 'expedido', 'correo',
            'telefono', 'pais_telefono', 'codigo_telefono', 'rol', 'password', 'password_confirmation',
            'usuarioId', 'fecha_ingreso', 'especialidad_salud', 'cargo_administrativo', 'observaciones'
        ]);
        $this->isEdit = false;
        $this->pasoFormulario = 1;
        $this->resetValidation();
    }

    public function crearUsuario()
    {
        $this->resetFormulario();
        $this->pais_documento = 'Bolivia';
        $this->tipo_documento = 'CI';
        $this->pais_telefono = 'Bolivia';
        $this->codigo_telefono = '+591';
        $this->estado = 'ACTIVO';
        $this->acceso_sistema = 'HABILITADO';
        $this->isEdit = false;
        $this->mostrarFormulario = true;
    }

    public function editarUsuario($cod_usu)
    {
        // ── Guardia: no editar usuarios inactivos ──
        $usuario = User::with(['personalSalud', 'personalAdmin'])->findOrFail($cod_usu);

        if ($usuario->estado !== 'ACTIVO') {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Acción no permitida',
                'text' => 'El usuario está inactivo. Debe activarlo antes de editar.'
            ]);
            return;
        }

        $this->resetValidation();
        $this->usuarioId = $cod_usu;
        $this->pasoFormulario = 1;
        $this->isEdit = true;

        $this->cod_usu = $usuario->cod_usu;
        $this->nombres = $usuario->nombres;
        $this->ap_paterno = $usuario->ap_paterno;
        $this->ap_materno = $usuario->ap_materno;
        $this->fecha_nacimiento = $usuario->fecha_nacimiento ? $usuario->fecha_nacimiento->format('Y-m-d') : '';
        $this->genero = $usuario->genero;
        $this->pais_documento = $usuario->pais_documento ?? 'Bolivia';
        $this->tipo_documento = $usuario->tipo_documento ?? 'CI';
        $this->numero_documento = $usuario->numero_documento;
        $this->expedido = $usuario->expedido;
        $this->correo = $usuario->correo;
        $this->telefono = $usuario->telefono;
        $this->pais_telefono = $usuario->pais_telefono ?? 'Bolivia';
        $this->codigo_telefono = $usuario->codigo_telefono ?? '+591';
        $this->estado = $usuario->estado;
        $this->acceso_sistema = $usuario->acceso_sistema;
        $this->observaciones = $usuario->observaciones;

        $this->rol = $usuario->roles->first()?->name ?? '';

        if ($this->rol === 'personal_salud') {
            $ps = $usuario->personalSalud;
            $this->fecha_ingreso = $ps?->fecha_ing ? $ps->fecha_ing->format('Y-m-d') : '';
            $this->especialidad_salud = $ps?->cod_esp;
        } elseif ($this->rol === 'personal_admin') {
            $pa = $usuario->personalAdmin;
            $this->fecha_ingreso = $pa?->fecha_ingreso ? $pa->fecha_ingreso->format('Y-m-d') : '';
            $this->cargo_administrativo = $pa?->cod_cargo_admin;
        }

        $this->password = '';
        $this->password_confirmation = '';

        // Cerrar paneles flotantes si estaban abiertos
        $this->cerrarFichaRapida();
        $this->cerrarVistaCompleta();

        $this->mostrarFormulario = true;
    }

    public function cerrarFormulario()
    {
        $this->resetValidation();
        $this->mostrarFormulario = false;
        $this->pasoFormulario = 1;
    }

    public function siguientePaso()
    {
        if ($this->pasoFormulario === 1) {
            $this->validate([
                'nombres' => ['required', 'string', 'max:255'],
                'ap_paterno' => ['required', 'string', 'max:255'],
                'fecha_nacimiento' => ['required', 'date', 'before:today'],
                'genero' => ['required', 'string'],
                'pais_documento' => ['required', 'string'],
                'tipo_documento' => ['required', 'string'],
                'numero_documento' => ['required', 'string', 'max:50'],
            ]);
        } elseif ($this->pasoFormulario === 2) {
            $this->validate([
                'correo' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'correo')->ignore($this->usuarioId, 'cod_usu')],
                'telefono' => ['required', 'string', 'max:20'],
            ]);
        } elseif ($this->pasoFormulario === 3) {
            $rules = [
                'rol' => ['required', 'exists:roles,name'],
                'acceso_sistema' => ['required', 'in:HABILITADO,BLOQUEADO'],
            ];
            if ($this->isEdit) {
                $rules['estado'] = ['required', 'in:ACTIVO,INACTIVO,ARCHIVADO'];
            }
            if ($this->rol === 'personal_salud') $rules['especialidad_salud'] = ['required', 'exists:especialidades,cod_esp'];
            if ($this->rol === 'personal_admin') $rules['cargo_administrativo'] = ['required', 'exists:cargos_administrativos,cod_cargo_admin'];
            $this->validate($rules);
        } elseif ($this->pasoFormulario === 4) {
            if ($this->isEdit) {
                $this->validate(['password' => ['nullable', 'string', 'min:8', 'confirmed']]);
            }
        }

        $this->pasoFormulario++;
    }

    public function anteriorPaso()
    {
        if ($this->pasoFormulario > 1) {
            $this->pasoFormulario--;
        }
    }

    public function irPaso($paso)
    {
        if ($paso < $this->pasoFormulario) {
            $this->pasoFormulario = $paso;
        }
    }

    private function generarPasswordTemporal(): string
    {
        $nombres = strtoupper(preg_replace('/\s+/', '', $this->nombres ?? 'USU'));
        $apPaterno = strtoupper(preg_replace('/\s+/', '', $this->ap_paterno ?? 'RM'));
        $documento = preg_replace('/\D/', '', $this->numero_documento ?? '');

        $parteNombre = substr($nombres, 0, 3);
        $parteApellido = substr($apPaterno, 0, 3);

        return $parteNombre . $parteApellido . ($documento ?: now()->format('His'));
    }

    public function guardarUsuario()
    {
        $rules = $this->rules();
        $this->validate($rules);

        $userData = [
            'nombres' => $this->nombres,
            'ap_paterno' => $this->ap_paterno,
            'ap_materno' => $this->ap_materno,
            'fecha_nacimiento' => $this->fecha_nacimiento,
            'genero' => $this->genero,
            'pais_documento' => $this->pais_documento,
            'tipo_documento' => $this->tipo_documento,
            'numero_documento' => $this->numero_documento,
            'expedido' => $this->expedido,
            'correo' => $this->correo,
            'telefono' => $this->telefono,
            'pais_telefono' => $this->pais_telefono,
            'codigo_telefono' => $this->codigo_telefono,
            'acceso_sistema' => $this->acceso_sistema,
            'observaciones' => $this->observaciones,
        ];

        $mensaje = '';

        if ($this->isEdit) {
            $userData['estado'] = $this->estado;
            if (!empty($this->password)) {
                $userData['password'] = Hash::make($this->password);
            }

            $usuario = User::findOrFail($this->usuarioId);

            if ($usuario->hasRole('super_admin') && $this->rol !== 'super_admin') {
                $superadmins = User::role('super_admin')->where('estado', 'ACTIVO')->count();
                if ($superadmins <= 1) {
                    $this->addError('rol', 'No puedes quitar el rol de superadministrador al único superadministrador activo.');
                    return;
                }
            }

            $usuario->update($userData);
            $usuario->syncRoles([$this->rol]);

            // Actualizar sub-modelos
            if ($this->rol === 'personal_salud') {
                \App\Models\PersonalSalud::updateOrCreate(
                    ['cod_usu' => $usuario->cod_usu],
                    ['fecha_ing' => $this->fecha_ingreso, 'cod_esp' => $this->especialidad_salud]
                );
                \App\Models\PersonalAdmin::where('cod_usu', $usuario->cod_usu)->delete();
            } elseif ($this->rol === 'personal_admin') {
                \App\Models\PersonalAdmin::updateOrCreate(
                    ['cod_usu' => $usuario->cod_usu],
                    ['fecha_ingreso' => $this->fecha_ingreso, 'cod_cargo_admin' => $this->cargo_administrativo]
                );
                \App\Models\PersonalSalud::where('cod_usu', $usuario->cod_usu)->delete();
            } else {
                \App\Models\PersonalSalud::where('cod_usu', $usuario->cod_usu)->delete();
                \App\Models\PersonalAdmin::where('cod_usu', $usuario->cod_usu)->delete();
            }

            // Registrar en bitácora
            if (function_exists('activity')) {
                activity('Usuarios')
                    ->causedBy(auth()->user())
                    ->performedOn($usuario)
                    ->event('edicion')
                    ->log("Se actualizó la información del usuario {$usuario->name}.");
            }

            $mensaje = 'Usuario actualizado correctamente.';
        } else {
            $userData['estado'] = 'ACTIVO';
            $passwordTemporal = $this->generarPasswordTemporal();
            $userData['password'] = Hash::make($passwordTemporal);

            $usuario = User::create($userData);
            $usuario->assignRole($this->rol);

            if ($this->rol === 'personal_salud') {
                \App\Models\PersonalSalud::create([
                    'cod_usu' => $usuario->cod_usu,
                    'fecha_ing' => $this->fecha_ingreso,
                    'cod_esp' => $this->especialidad_salud
                ]);
            } elseif ($this->rol === 'personal_admin') {
                \App\Models\PersonalAdmin::create([
                    'cod_usu' => $usuario->cod_usu,
                    'fecha_ingreso' => $this->fecha_ingreso,
                    'cod_cargo_admin' => $this->cargo_administrativo
                ]);
            }

            // Registrar en bitácora
            if (function_exists('activity')) {
                activity('Usuarios')
                    ->causedBy(auth()->user())
                    ->performedOn($usuario)
                    ->event('registro')
                    ->log("Se registró un nuevo usuario: {$usuario->name} con rol {$this->rol}.");
            }

            $mensaje = "Usuario registrado correctamente. Contraseña temporal: {$passwordTemporal}";
            $this->resetPage();
        }

        $this->resetFormulario();
        $this->mostrarFormulario = false;

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Éxito',
            'text' => $mensaje
        ]);
    }

    // ══════════════════════════════════════════════
    // VISTA COMPLETA (modal/panel expandido)
    // ══════════════════════════════════════════════

    public function abrirVistaCompleta($codUsu): void
    {
        $this->usuarioVista = User::with([
            'roles',
            'personalSalud.especialidad',
            'personalAdmin.cargoAdmin',
            'voluntarios',
            'familiares',
        ])->where('cod_usu', $codUsu)->firstOrFail();

        $this->mostrarVistaCompleta = true;
    }

    public function cerrarVistaCompleta(): void
    {
        $this->mostrarVistaCompleta = false;
        $this->usuarioVista = null;
    }

    // ══════════════════════════════════════════════
    // FICHA RÁPIDA FLOTANTE (panel lateral derecho)
    // ══════════════════════════════════════════════

    public function abrirFichaRapida($codUsu): void
    {
        $this->usuarioFichaId = $codUsu;
        $this->usuarioFicha = User::with([
            'roles',
            'personalSalud.especialidad',
            'personalAdmin.cargoAdmin',
        ])->where('cod_usu', $codUsu)->firstOrFail();
        $this->mostrarFichaRapida = true;
    }

    public function cerrarFichaRapida(): void
    {
        $this->mostrarFichaRapida = false;
        $this->usuarioFichaId = null;
        $this->usuarioFicha = null;
    }

    // ══════════════════════════════════════════════
    // FILTROS
    // ══════════════════════════════════════════════

    public function aplicarFiltros(): void
    {
        $this->resetPage();
    }

    public function limpiarFiltros(): void
    {
        $this->reset(['search', 'filtroRol', 'filtroEstado', 'filtroGenero']);
        $this->resetPage();
    }

    // ══════════════════════════════════════════════
    // HOOKS DE ACTUALIZACIÓN
    // ══════════════════════════════════════════════

    public function updatedPaisTelefono($value)
    {
        $codigos = [
            'Bolivia' => '+591',
            'Brasil' => '+55',
            'Argentina' => '+54',
            'Perú' => '+51',
            'Chile' => '+56',
            'Colombia' => '+57',
            'México' => '+52'
        ];
        $this->codigo_telefono = $codigos[$value] ?? '+591';
    }

    public function updatedPaisDocumento()
    {
        if ($this->pais_documento !== 'Bolivia') {
            $this->expedido = null;
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFiltroRol()
    {
        $this->resetPage();
    }

    public function updatingFiltroEstado()
    {
        $this->resetPage();
    }

    // ══════════════════════════════════════════════
    // TOGGLE ESTADO (ACTIVAR / INACTIVAR)
    // ══════════════════════════════════════════════

    public function toggleEstado($id)
    {
        $usuario = User::findOrFail($id);

        // No permitir autoinactivación
        if ($usuario->cod_usu === auth()->id()) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acción denegada',
                'text' => 'No puedes cambiar tu propio estado.'
            ]);
            return;
        }

        // Proteger último super_admin
        if ($usuario->estado === 'ACTIVO' && $usuario->hasRole('super_admin')) {
            $superadmins = User::role('super_admin')->where('estado', 'ACTIVO')->count();
            if ($superadmins <= 1) {
                $this->dispatch('swal', [
                    'icon' => 'error',
                    'title' => 'Acción denegada',
                    'text' => 'No puedes desactivar al único superadministrador activo.'
                ]);
                return;
            }
        }

        $nuevoEstado = $usuario->estado === 'ACTIVO' ? 'INACTIVO' : 'ACTIVO';
        $usuario->update(['estado' => $nuevoEstado]);

        // Registrar en bitácora
        if (function_exists('activity')) {
            activity('Usuarios')
                ->causedBy(auth()->user())
                ->performedOn($usuario)
                ->event($nuevoEstado === 'ACTIVO' ? 'activacion' : 'inactivacion')
                ->log("Se cambió el estado de {$usuario->name} a {$nuevoEstado}.");
        }

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Estado actualizado',
            'text' => "El usuario ahora está {$nuevoEstado}."
        ]);
    }

    // ══════════════════════════════════════════════
    // RENDER
    // ══════════════════════════════════════════════

    public function render()
    {
        $query = User::query()->with([
            'roles',
            'personalSalud.especialidad',
            'personalAdmin.cargoAdmin',
        ]);

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('nombres', 'ilike', '%' . $this->search . '%')
                  ->orWhere('ap_paterno', 'ilike', '%' . $this->search . '%')
                  ->orWhere('ap_materno', 'ilike', '%' . $this->search . '%')
                  ->orWhere('correo', 'ilike', '%' . $this->search . '%')
                  ->orWhere('cod_usu', 'ilike', '%' . $this->search . '%');
            });
        }

        if (!empty($this->filtroEstado)) {
            $query->where('estado', $this->filtroEstado);
        }

        if (!empty($this->filtroRol)) {
            $query->role($this->filtroRol);
        }

        if (!empty($this->filtroGenero)) {
            $query->where('genero', $this->filtroGenero);
        }

        // Ordenamiento: ACTIVOS primero, luego alfabético
        $query->orderByRaw("CASE WHEN estado = 'ACTIVO' THEN 0 ELSE 1 END")
              ->orderBy('nombres')
              ->orderBy('ap_paterno')
              ->orderBy('ap_materno');

        return view('livewire.admin.usuarios.usuarios-panel', [
            'usuarios' => $query->paginate(12),
            'roles' => Role::all(),
            'especialidades' => \App\Models\Especialidad::all(),
            'cargosAdmin' => \App\Models\CargoAdministrativo::all()
        ]);
    }
}
