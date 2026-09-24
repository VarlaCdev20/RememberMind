<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Jetstream\HasProfilePhoto;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasProfilePhoto, HasRoles, LogsActivity, Notifiable;

        protected static function booted(): void
    {
        static::creating(function (self $user): void {
            if (empty($user->cod_usuario)) {
                $user->cod_usuario = 'USU_' . strtoupper(\Illuminate\Support\Str::random(10));
            }
            if (empty($user->estado)) {
                $user->estado = 'ACTIVO';
            }
        });
    }

public static array $areasEstaticas = [
        'ARE_0001' => 'DIRECCIÓN GENERAL',
        'ARE_0002' => 'COORDINACIÓN DE PROGRAMAS Y SERVICIOS',
        'ARE_0003' => 'ÁREA ADMINISTRATIVA Y REGISTRO INSTITUCIONAL',
        'ARE_0004' => 'ÁREA DE ATENCIÓN MÉDICA',
        'ARE_0005' => 'ÁREA DE PSICOLOGÍA Y SEGUIMIENTO COGNITIVO',
    ];

    protected $table = 'usuarios';
    protected $primaryKey = 'cod_usuario';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['cod_usuario', 'correo', 'contrasena', 'password', 'foto', 'estado'];
    protected $hidden = ['contrasena'];
    protected $appends = ['nombres', 'ap_paterno', 'ap_materno'];

    protected function casts(): array
    {
        return [];
    }

    public function getAuthIdentifierName(): string
    {
        return 'cod_usuario';
    }

    public function getAuthPasswordName(): string
    {
        return 'contrasena';
    }

    public function getAuthPassword(): string
    {
        return (string) $this->contrasena;
    }

    public function getRememberToken(): ?string
    {
        return null;
    }

    public function setRememberToken($value): void
    {
        // El baseline congelado no incluye remember_token.
    }

    public function getRouteKeyName(): string
    {
        return 'cod_usuario';
    }

    public function getEmailForPasswordReset(): string
    {
        return $this->correo;
    }

    public function routeNotificationForMail(): string
    {
        return $this->correo;
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function getNameAttribute(): string
    {
        $persona = $this->personal ?: $this->contactos()->first();

        return $persona
            ? trim("{$persona->nombres} {$persona->apellido_paterno} {$persona->apellido_materno}")
            : $this->correo;
    }

    /**
     * Alias de lectura para las vistas históricas. La clave persistida de V2
     * continúa siendo cod_usuario; este alias no crea una columna legacy.
     */

    public function setCodUsuAttribute($value): void
    {
        $this->attributes['cod_usuario'] = $value;
    }

    public function getCodUsuAttribute(): string
    {
        return (string) $this->cod_usuario;
    }

    /**
     * Fortify y algunos componentes de Jetstream todavía consultan password.
     * La contraseña real permanece en usuarios.contrasena.
     */
    public function setPasswordAttribute($value): void
    {
        if ($value === null || $value === '') {
            return;
        }
        $this->attributes['contrasena'] = \Illuminate\Support\Facades\Hash::isHashed($value)
            ? $value
            : \Illuminate\Support\Facades\Hash::make($value);
    }

    public function setContrasenaAttribute($value): void
    {
        if ($value === null || $value === '') {
            return;
        }
        $this->attributes['contrasena'] = \Illuminate\Support\Facades\Hash::isHashed($value)
            ? $value
            : \Illuminate\Support\Facades\Hash::make($value);
    }

    public function getPasswordAttribute(): string
    {
        return (string) $this->contrasena;
    }

    public function getNombresAttribute(): string
    {
        return (string) ($this->personal?->nombres ?? $this->contactos()->value('nombres') ?? '');
    }

    public function getApPaternoAttribute(): string
    {
        return (string) ($this->personal?->apellido_paterno ?? $this->contactos()->value('apellido_paterno') ?? '');
    }

    public function getApMaternoAttribute(): string
    {
        return (string) ($this->personal?->apellido_materno ?? $this->contactos()->value('apellido_materno') ?? '');
    }

    public function getFotoDePerfilAttribute(): ?string
    {
        return $this->foto;
    }

    public function getProfilePhotoPathAttribute(): ?string
    {
        return $this->foto;
    }

    public function personal(): HasOne
    {
        return $this->hasOne(Personal::class, 'cod_usuario', 'cod_usuario');
    }

    public function contactos(): HasMany
    {
        return $this->hasMany(Contacto::class, 'cod_usuario', 'cod_usuario');
    }

    /** Alias de interfaz; los datos se conservan en contactos V2. */
    public function familiares(): HasMany
    {
        return $this->contactos();
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class, 'cod_usuario', 'cod_usuario');
    }

    /**
     * Adaptadores de lectura para el perfil histórico. En V2 el área y el
     * horario se obtienen a través de personal -> asignaciones_personal.
     */
    public function areaInstitucional(): HasOne
    {
        return $this->hasOne(Personal::class, 'cod_usuario', 'cod_usuario')
            ->with('asignaciones.area');
    }

    public function horariosSalud(): HasManyThrough
    {
        return $this->asignacionesV2();
    }

    public function horariosAdmin(): HasManyThrough
    {
        return $this->asignacionesV2();
    }

    /** Relación V2 usada por el panel institucional restaurado. */
    public function asignacionesTurno(): HasManyThrough
    {
        return $this->asignacionesV2();
    }

    private function asignacionesV2(): HasManyThrough
    {
        return $this->hasManyThrough(
            AsignacionPersonal::class,
            Personal::class,
            'cod_usuario',
            'cod_personal',
            'cod_usuario',
            'cod_personal',
        )->with(['jornada.turno', 'area']);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['correo', 'foto', 'estado'])->logOnlyDirty();
    }
    public function scopeOrderByNombre($query, string $direction = 'asc')
    {
        return $query->leftJoin('personal', 'usuarios.cod_usuario', '=', 'personal.cod_usuario')
            ->orderBy('personal.nombres', $direction)
            ->orderBy('personal.apellido_paterno', $direction)
            ->select('usuarios.*');
    }

    public function scopeOrderByApellidoPaterno($query, string $direction = 'asc')
    {
        return $query->leftJoin('personal', 'usuarios.cod_usuario', '=', 'personal.cod_usuario')
            ->orderBy('personal.apellido_paterno', $direction)
            ->orderBy('personal.nombres', $direction)
            ->select('usuarios.*');
    }
}
