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

    public $incrementing = true;
    protected $keyType = 'int';

    // Columnas verificadas contra la BD real (2026-06-04).
    // Las columnas pendientes de migración están fuera de esta lista.
    protected $fillable = [
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
        // Columnas pendientes de migración — agregar cuando corran:
        // 'debe_cambiar_password',       → 2026_05_17_215318
        // 'password_changed_at',         → 2026_05_17_215318
        // 'cod_area',                    → sin migración aún
        // 'direccion',                   → 2026_05_18_020000
        // 'zona',                        → 2026_05_18_020000
        // 'ciudad',                      → 2026_05_18_020000
        // 'contacto_emergencia',         → 2026_05_18_020000
        // 'parentesco_emergencia',       → 2026_05_18_020000
        // 'celular_emergencia',          → 2026_05_18_020000
        // 'tipo_vinculacion',            → 2026_05_18_020000
        // 'calle',                       → 2026_05_18_150000
        // 'nro_domicilio',               → 2026_05_18_150000
        // 'ap_paterno_emergencia',       → 2026_05_18_150000
        // 'ap_materno_emergencia',       → 2026_05_18_150000
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'ultimo_acceso' => 'datetime',
            'fecha_nacimiento' => 'date',
            'password' => 'hashed',
            // 'debe_cambiar_password' => 'boolean', // activar tras migración 2026_05_17_215318
            // 'password_changed_at' => 'datetime',  // activar tras migración 2026_05_17_215318
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
            ->logOnly(['nombres', 'ap_paterno', 'correo', 'estado', 'observaciones'])
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

    public function areaInstitucional()
    {
        return $this->belongsTo(AreaInstitucional::class, 'cod_area', 'cod_area');
    }

    public function documentos()
    {
        return $this->hasMany(DocumentoUsuario::class, 'cod_usu', 'cod_usu');
    }

    public function documentosPendientes()
    {
        return $this->hasMany(DocumentoUsuario::class, 'cod_usu', 'cod_usu')
            ->whereIn('estado', [DocumentoUsuario::ESTADO_PENDIENTE, DocumentoUsuario::ESTADO_CARGADO]);
    }

    public function documentosAprobados()
    {
        return $this->hasMany(DocumentoUsuario::class, 'cod_usu', 'cod_usu')
            ->where('estado', DocumentoUsuario::ESTADO_APROBADO);
    }

    public function documentosObservados()
    {
        return $this->hasMany(DocumentoUsuario::class, 'cod_usu', 'cod_usu')
            ->where('estado', DocumentoUsuario::ESTADO_OBSERVADO);
    }

    public function familiares()
    {
        return $this->hasMany(Familiar::class, 'cod_usu', 'cod_usu');
    }

    public function voluntarios()
    {
        return $this->hasMany(Voluntario::class, 'cod_usu', 'cod_usu');
    }

    public function personalSalud()
    {
        return $this->hasOne(PersonalSalud::class, 'cod_usu', 'cod_usu');
    }

    public function personalAdmin()
    {
        return $this->hasOne(PersonalAdmin::class, 'cod_usu', 'cod_usu');
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

    public function asignacionesTurno()
    {
        return $this->hasMany(AsignacionTurno::class, 'cod_usu', 'cod_usu');
    }

    public function turnosInstitucionales()
    {
        return $this->hasManyThrough(TurnoInstitucional::class, AsignacionTurno::class, 'cod_usu', 'cod_turno', 'cod_usu', 'cod_turno');
    }
}