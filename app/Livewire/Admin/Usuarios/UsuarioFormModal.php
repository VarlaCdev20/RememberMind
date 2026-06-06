<?php

namespace App\Livewire\Admin\Usuarios;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Livewire\Component;
use Illuminate\Validation\Rule;

class UsuarioFormModal extends Component
{
    public bool $mostrar = false;
    public ?string $usuarioId = null;
    public $isEdit = false;
    
    public $cod_usu;
    public $nombres;
    public $ap_paterno;
    public $ap_materno;
    public $correo;
    public $telefono;
    public $rol;
    public $estado = 'ACTIVO';
    public $password;
    public $password_confirmation;

    // Propiedades para actualización de contraseña
    public $showPasswordSection = false;
    public $new_password;
    public $new_password_confirmation;
    public $forzar_cambio = false;
    public $temp_password_generated = null;

    protected $listeners = [
        'usuario-form-abrir' => 'abrir',
    ];

    public function rules()
    {
        $rules = [
            'nombres' => ['required', 'string', 'max:255'],
            'ap_paterno' => ['nullable', 'string', 'max:255'],
            'ap_materno' => ['nullable', 'string', 'max:255'],
            'correo' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($this->cod_usu, 'cod_usu')],
            'telefono' => ['nullable', 'string', 'max:20'],
            'rol' => ['required', 'exists:roles,name'],
            'estado' => ['required', 'in:ACTIVO,INACTIVO'],
        ];

        if (!$this->isEdit) {
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        } else {
            $rules['password'] = ['nullable', 'string', 'min:8', 'confirmed'];
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'nombres.required' => 'El nombre es obligatorio.',
            'correo.required' => 'El correo es obligatorio.',
            'correo.email' => 'El correo debe ser válido.',
            'correo.unique' => 'Este correo ya está registrado.',
            'rol.required' => 'Debe seleccionar un rol.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ];
    }

    public function abrir($usuarioId = null)
    {
        $this->resetValidation();
        $this->reset(['cod_usu', 'nombres', 'ap_paterno', 'ap_materno', 'correo', 'telefono', 'rol', 'password', 'password_confirmation', 'usuarioId']);
        $this->estado = 'ACTIVO';
        
        $this->usuarioId = $usuarioId;

        if ($usuarioId) {
            $this->isEdit = true;
            $usuario = User::findOrFail($usuarioId);
            
            $this->cod_usu = $usuario->cod_usu;
            $this->nombres = $usuario->nombres;
            $this->ap_paterno = $usuario->ap_paterno;
            $this->ap_materno = $usuario->ap_materno;
            $this->correo = $usuario->correo;
            $this->telefono = $usuario->telefono;
            $this->estado = $usuario->estado;
            
            $this->rol = $usuario->roles->first()?->name ?? '';
            
            $this->password = '';
            $this->password_confirmation = '';
        } else {
            $this->isEdit = false;
        }

        $this->showPasswordSection = false;
        $this->reset(['new_password', 'new_password_confirmation', 'temp_password_generated', 'forzar_cambio']);

        $this->mostrar = true;
    }

    public function cerrar()
    {
        $this->mostrar = false;
        $this->showPasswordSection = false;
        $this->resetValidation();
        $this->reset(['new_password', 'new_password_confirmation', 'temp_password_generated', 'forzar_cambio']);
    }

    public function togglePasswordSection()
    {
        $this->showPasswordSection = !$this->showPasswordSection;
        $this->reset(['new_password', 'new_password_confirmation', 'temp_password_generated', 'forzar_cambio']);
        $this->resetValidation(['new_password', 'new_password_confirmation']);
    }

    public function generarPasswordTemporal()
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
        $pass = substr(str_shuffle($chars), 0, 12);
        $this->new_password = $pass;
        $this->new_password_confirmation = $pass;
        $this->temp_password_generated = $pass;
        $this->forzar_cambio = true;
    }

    public function actualizarPassword()
    {
        if (!auth()->user()->can('usuarios.editar')) {
            abort(403, 'No tienes permiso para editar usuarios.');
        }

        $this->validate([
            'new_password' => 'required|min:8|same:new_password_confirmation',
        ], [
            'new_password.required' => 'La nueva contraseña es obligatoria.',
            'new_password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'new_password.same' => 'Las contraseñas no coinciden.',
        ]);

        $usuario = User::findOrFail($this->usuarioId);
        
        $usuario->password = $this->new_password;
        $usuario->debe_cambiar_password = $this->forzar_cambio;
        $usuario->save();

        $this->togglePasswordSection();
        
        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Éxito',
            'text' => 'Contraseña actualizada correctamente.'
        ]);
    }

    public function guardar()
    {
        $this->validate();

        if ($this->isEdit) {
            $usuario = User::findOrFail($this->cod_usu);
            
            // Proteger último admin
            if ($usuario->hasRole('SUPERADMINISTRADOR') && $this->rol !== 'SUPERADMINISTRADOR') {
                $superadmins = User::role('SUPERADMINISTRADOR')->where('estado', 'ACTIVO')->count();
                if ($superadmins <= 1) {
                    $this->addError('rol', 'No puedes quitar el rol de superadministrador al único superadministrador activo.');
                    return;
                }
            }

            $data = [
                'nombres' => $this->nombres,
                'ap_paterno' => $this->ap_paterno,
                'ap_materno' => $this->ap_materno,
                'correo' => $this->correo,
                'telefono' => $this->telefono,
                'estado' => $this->estado,
            ];

            if (!empty($this->password)) {
                $data['password'] = $this->password;
            }

            $usuario->update($data);
            $usuario->syncRoles([$this->rol]);

            $mensaje = 'Usuario actualizado correctamente.';
        } else {
            $usuario = User::create([
                'nombres' => $this->nombres,
                'ap_paterno' => $this->ap_paterno,
                'ap_materno' => $this->ap_materno,
                'correo' => $this->correo,
                'telefono' => $this->telefono,
                'estado' => $this->estado,
                'password' => $this->password,
            ]);
            
            $usuario->assignRole($this->rol);
            $mensaje = 'Usuario registrado correctamente.';
        }

        $this->mostrar = false;
        $this->dispatch('usuario-guardado');
        
        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Éxito',
            'text' => $mensaje
        ]);
    }

    public function render()
    {
        return view('livewire.admin.usuarios.usuario-form-modal', [
            'roles' => Role::all()
        ]);
    }
}
