<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasProfilePhoto, Notifiable, HasRoles, LogsActivity;

    protected $table = 'users';
    protected $primaryKey = 'cod_usu';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'cod_usu',
        'nombres',
        'ap_paterno',
        'ap_materno',
        'pais_documento',
        'tipo_documento',
        'numero_documento',
        'expedido',
        'correo',
        'password',
        'telefono',
        'pais_telefono',
        'codigo_telefono',
        'genero',
        'fecha_nacimiento',
        'foto_de_perfil',
        'estado',
        'acceso_sistema',
        'ultimo_acceso',
        'observaciones',
        'current_team_id',
        'cod_area',
        'debe_cambiar_password',
        'password_changed_at',
        'direccion',
        'zona',
        'ciudad',
        'contacto_emergencia',
        'parentesco_emergencia',
        'celular_emergencia',
        'tipo_vinculacion',
        'calle',
        'nro_domicilio',
        'ap_paterno_emergencia',
        'ap_materno_emergencia',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected static function booted(): void
    {
        static::creating(function ($usuario) {
            if (!$usuario->cod_usu) {
                $ultimo = self::where('cod_usu', 'like', 'USU_%')
                    ->orderByDesc('cod_usu')
                    ->value('cod_usu');

                $numero = $ultimo
                    ? ((int) substr($ultimo, 4)) + 1
                    : 1;

                $usuario->cod_usu = 'USU_' . str_pad($numero, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'ultimo_acceso' => 'datetime',
            'fecha_nacimiento' => 'date',
            'password' => 'hashed',
            'debe_cambiar_password' => 'boolean',
            'password_changed_at' => 'datetime',
        ];
    }

    public function getAuthIdentifierName()
    {
        return 'cod_usu';
    }

    public function getRouteKeyName()
    {
        return 'cod_usu';
    }

    public function getNameAttribute(): string
    {
        return trim($this->nombres . ' ' . ($this->ap_paterno ?? '') . ' ' . ($this->ap_materno ?? ''));
    }

    public function getEmailAttribute(): string
    {
        return $this->correo;
    }

    public function getProfilePhotoUrlAttribute(): string
    {
        return $this->foto_de_perfil
            ? asset('storage/' . $this->foto_de_perfil)
            : $this->defaultProfilePhotoUrl();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nombres', 'ap_paterno', 'correo', 'estado', 'observaciones', 'cod_area'])
            ->logOnlyDirty()
            ->useLogName('Usuarios')
            ->setDescriptionForEvent(function (string $eventName) {
                $nombre = trim($this->nombres . ' ' . ($this->ap_paterno ?? ''));
                
                if ($eventName === 'created') {
                    return "Se registró al nuevo usuario institucional: {$nombre}.";
                }
                
                if ($eventName === 'updated') {
                    if ($this->wasChanged('estado')) {
                        $nuevoEstado = $this->estado == 1 ? 'ACTIVO' : 'INACTIVO';
                        return "Se cambió el estado del usuario {$this->cod_usu} a {$nuevoEstado}.";
                    }
                    return "Se actualizaron los datos del usuario: {$nombre}.";
                }
                
                if ($eventName === 'deleted') {
                    return "Se eliminó el registro del usuario: {$nombre}.";
                }

                return "Usuario {$this->cod_usu} fue modificado ({$eventName}).";
            });
    }

    public function documentos()
    {
        return $this->hasMany(DocumentoUsuario::class, 'cod_usu', 'cod_usu');
    }

    public function documentosPendientes()
    {
        return $this->hasMany(DocumentoUsuario::class, 'cod_usu', 'cod_usu')->whereIn('estado', ['PENDIENTE', 'CARGADO']);
    }

    public function documentosValidados()
    {
        return $this->hasMany(DocumentoUsuario::class, 'cod_usu', 'cod_usu')->where('estado', 'VALIDADO');
    }

    public function familiares()
    {
        return $this->hasMany(Familiar::class, 'cod_usu', 'cod_usu');
    }

    public function voluntarios()
    {
        return $this->hasMany(Voluntario::class, 'cod_usu', 'cod_usu');
    }

    // ── Relaciones FASE 2: Registros médicos/administrativos realizados por este usuario ──

    public function fichasMedicasRegistradas()
    {
        return $this->hasMany(FichaMedicaAdulto::class, 'registrado_por', 'cod_usu');
    }

    public function medicacionesRegistradas()
    {
        return $this->hasMany(MedicacionAdulto::class, 'registrado_por', 'cod_usu');
    }

    public function administracionesMedicacionRegistradas()
    {
        return $this->hasMany(AdministracionMedicacion::class, 'registrado_por', 'cod_usu');
    }

    public function signosVitalesRegistrados()
    {
        return $this->hasMany(SignosVitalesAdulto::class, 'registrado_por', 'cod_usu');
    }

    public function valoracionesFuncionalesRegistradas()
    {
        return $this->hasMany(ValoracionFuncionalAdulto::class, 'registrado_por', 'cod_usu');
    }

    public function cambiosEstadoAdultoRealizados()
    {
        return $this->hasMany(HistorialEstadoAdulto::class, 'cambiado_por', 'cod_usu');
    }

    public function horariosSalud()
    {
        return $this->hasMany(HorarioPersonalSalud::class, 'cod_usu', 'cod_usu');
    }

    public function horariosAdmin()
    {
        return $this->hasMany(HorarioPersonalAdmin::class, 'cod_usu', 'cod_usu');
    }

    // ── VIRTUAL RELATIONS FOR ADAPTATION ──

    public static array $areasEstaticas = [
        'ARE_0001' => 'DIRECCIÓN GENERAL',
        'ARE_0002' => 'COORDINACIÓN DE PROGRAMAS Y SERVICIOS',
        'ARE_0003' => 'ÁREA ADMINISTRATIVA Y REGISTRO INSTITUCIONAL',
        'ARE_0004' => 'ÁREA DE ATENCIÓN MÉDICA',
        'ARE_0005' => 'ÁREA DE PSICOLOGÍA Y SEGUIMIENTO COGNITIVO',
    ];

    public function getPersonalSaludAttribute()
    {
        $rol = $this->roles->first()?->name;
        if (in_array($rol, ['ENFERMEROS', 'MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A', 'PEDAGOGO', 'NUTRICIONISTA', 'FISIOTERAPEUTA'])) {
            return (object) [
                'especialidad' => (object) [
                    'nombre' => $rol,
                    'cod_esp' => $rol,
                ],
                'cod_esp' => $rol,
                'fecha_ing' => $this->created_at,
                'institucion_formacion' => $this->observaciones,
            ];
        }
        return null;
    }

    public function getPersonalAdminAttribute()
    {
        $rol = $this->roles->first()?->name;
        if (in_array($rol, ['SUPERADMINISTRADOR', 'ADMINISTRADOR'])) {
            return (object) [
                'cargoAdmin' => (object) [
                    'nombre' => $rol,
                    'cod_cargo_admin' => $rol,
                ],
                'cargo' => $rol,
                'cod_cargo_admin' => $rol,
                'fecha_ingreso' => $this->created_at,
            ];
        }
        return null;
    }

    public function getAreaInstitucionalAttribute()
    {
        if ($this->cod_area && isset(self::$areasEstaticas[$this->cod_area])) {
            return (object) [
                'nombre' => self::$areasEstaticas[$this->cod_area],
                'cod_area' => $this->cod_area,
            ];
        }
        return null;
    }

    public function getAsignacionesTurnoAttribute()
    {
        $salud = $this->horariosSalud;
        $admin = $this->horariosAdmin;

        $mapped = collect();

        foreach ($salud as $h) {
            $mapped->push((object) [
                'estado' => $h->estado === 'ACTIVO' ? 'ACTIVA' : 'INACTIVA',
                'turno' => (object) [
                    'nombre' => $h->turno,
                ],
            ]);
        }

        foreach ($admin as $h) {
            $mapped->push((object) [
                'estado' => $h->estado === 'ACTIVO' ? 'ACTIVA' : 'INACTIVA',
                'turno' => (object) [
                    'nombre' => $h->turno,
                ],
            ]);
        }

        return $mapped;
    }
}