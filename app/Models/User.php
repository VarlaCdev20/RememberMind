<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use TwoFactorAuthenticatable;
    use Notifiable;
    use HasRoles;
    use LogsActivity;

    protected $table = 'users';

    protected $primaryKey = 'cod_usu';

    public $incrementing = false;

    protected $keyType = 'string';


    /**
     * Campos permitidos para asignación masiva.
     *
     * @var array<int, string>
     */
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


    /**
     * Campos ocultos al serializar el modelo.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',

        /*
         * Datos sensibles de autenticación en dos pasos.
         */
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];


    /**
     * Eventos del modelo.
     */
    protected static function booted(): void
    {
        static::creating(function (User $usuario): void {
            if ($usuario->cod_usu) {
                return;
            }

            $ultimo = self::query()
                ->where(
                    'cod_usu',
                    'like',
                    'USU_%'
                )
                ->orderByDesc(
                    'cod_usu'
                )
                ->value(
                    'cod_usu'
                );

            $numero = $ultimo
                ? ((int) substr($ultimo, 4)) + 1
                : 1;

            $usuario->cod_usu =
                'USU_'
                . str_pad(
                    (string) $numero,
                    4,
                    '0',
                    STR_PAD_LEFT
                );
        });
    }


    /**
     * Casts del modelo.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'ultimo_acceso' => 'datetime',
            'fecha_nacimiento' => 'date',

            /*
             * Laravel hashea automáticamente cualquier
             * contraseña asignada directamente al modelo.
             */
            'password' => 'hashed',

            'debe_cambiar_password' => 'boolean',
            'password_changed_at' => 'datetime',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | AUTENTICACIÓN
    |--------------------------------------------------------------------------
    */

    /**
     * Identificador utilizado por Laravel Auth.
     */
    public function getAuthIdentifierName(): string
    {
        return 'cod_usu';
    }


    /**
     * Identificador utilizado en Route Model Binding.
     */
    public function getRouteKeyName(): string
    {
        return 'cod_usu';
    }


    /*
    |--------------------------------------------------------------------------
    | NOMBRE / CORREO
    |--------------------------------------------------------------------------
    */

    /**
     * Nombre completo del usuario.
     */
    public function getNameAttribute(): string
    {
        return trim(
            $this->nombres
                . ' '
                . ($this->ap_paterno ?? '')
                . ' '
                . ($this->ap_materno ?? '')
        );
    }


    /**
     * Compatibilidad con componentes de Laravel que esperan
     * la propiedad convencional "email".
     */
    public function getEmailAttribute(): string
    {
        return $this->correo;
    }


    /*
    |--------------------------------------------------------------------------
    | RECUPERACIÓN DE CONTRASEÑA
    |--------------------------------------------------------------------------
    */

    /**
     * Correo utilizado por el Password Broker.
     *
     * Laravel usa "email" de forma convencional.
     * RememberMind utiliza "correo".
     */
    public function getEmailForPasswordReset(): string
    {
        return $this->correo;
    }


    /**
     * Dirección utilizada por el canal de notificaciones mail.
     */
    public function routeNotificationForMail(
        $notification = null
    ): string {
        return $this->correo;
    }


    /**
     * Enviar la notificación institucional de
     * recuperación de contraseña.
     */
    public function sendPasswordResetNotification(
        #[\SensitiveParameter]
        $token
    ): void {
        $this->notify(
            new ResetPasswordNotification(
                $token
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | FOTO DE PERFIL
    |--------------------------------------------------------------------------
    */

    /**
     * URL de la fotografía del usuario.
     */
    public function getProfilePhotoUrlAttribute(): string
    {
        return $this->foto_de_perfil
            ? asset(
                'storage/'
                    . $this->foto_de_perfil
            )
            : $this->defaultProfilePhotoUrl();
    }


    /*
    |--------------------------------------------------------------------------
    | AUDITORÍA
    |--------------------------------------------------------------------------
    */

    /**
     * Configuración del registro automático
     * de actividad del usuario.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'nombres',
                'ap_paterno',
                'correo',
                'estado',
                'observaciones',
                'cod_area',
            ])
            ->logOnlyDirty()
            ->useLogName(
                'Usuarios'
            )
            ->setDescriptionForEvent(
                function (string $eventName): string {
                    $nombre = trim(
                        $this->nombres
                            . ' '
                            . ($this->ap_paterno ?? '')
                    );


                    if (
                        $eventName === 'created'
                    ) {
                        return "Se registró al nuevo usuario institucional: {$nombre}.";
                    }


                    if (
                        $eventName === 'updated'
                    ) {
                        if (
                            $this->wasChanged(
                                'estado'
                            )
                        ) {
                            $nuevoEstado =
                                (string) $this->estado;

                            return "Se cambió el estado del usuario {$this->cod_usu} a {$nuevoEstado}.";
                        }


                        return "Se actualizaron los datos del usuario: {$nombre}.";
                    }


                    if (
                        $eventName === 'deleted'
                    ) {
                        return "Se eliminó el registro del usuario: {$nombre}.";
                    }


                    return "Usuario {$this->cod_usu} fue modificado ({$eventName}).";
                }
            );
    }


    /*
    |--------------------------------------------------------------------------
    | DOCUMENTOS
    |--------------------------------------------------------------------------
    */

    public function documentos()
    {
        return $this->hasMany(
            DocumentoUsuario::class,
            'cod_usu',
            'cod_usu'
        );
    }


    public function documentosPendientes()
    {
        return $this->hasMany(
            DocumentoUsuario::class,
            'cod_usu',
            'cod_usu'
        )->whereIn(
            'estado',
            [
                'PENDIENTE',
                'CARGADO',
            ]
        );
    }


    public function documentosValidados()
    {
        return $this->hasMany(
            DocumentoUsuario::class,
            'cod_usu',
            'cod_usu'
        )->where(
            'estado',
            'VALIDADO'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | RELACIONES GENERALES
    |--------------------------------------------------------------------------
    */

    public function familiares()
    {
        return $this->hasMany(
            Familiar::class,
            'cod_usu',
            'cod_usu'
        );
    }


    public function voluntarios()
    {
        return $this->hasMany(
            Voluntario::class,
            'cod_usu',
            'cod_usu'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | REGISTROS MÉDICOS / ADMINISTRATIVOS
    |--------------------------------------------------------------------------
    */

    public function fichasMedicasRegistradas()
    {
        return $this->hasMany(
            FichaMedicaAdulto::class,
            'registrado_por',
            'cod_usu'
        );
    }


    public function medicacionesRegistradas()
    {
        return $this->hasMany(
            MedicacionAdulto::class,
            'registrado_por',
            'cod_usu'
        );
    }


    public function administracionesMedicacionRegistradas()
    {
        return $this->hasMany(
            AdministracionMedicacion::class,
            'registrado_por',
            'cod_usu'
        );
    }


    public function signosVitalesRegistrados()
    {
        return $this->hasMany(
            SignosVitalesAdulto::class,
            'registrado_por',
            'cod_usu'
        );
    }


    public function valoracionesFuncionalesRegistradas()
    {
        return $this->hasMany(
            ValoracionFuncionalAdulto::class,
            'registrado_por',
            'cod_usu'
        );
    }


    public function cambiosEstadoAdultoRealizados()
    {
        return $this->hasMany(
            HistorialEstadoAdulto::class,
            'cambiado_por',
            'cod_usu'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | HORARIOS
    |--------------------------------------------------------------------------
    */

    public function horariosSalud()
    {
        return $this->hasMany(
            HorarioPersonalSalud::class,
            'cod_usu',
            'cod_usu'
        );
    }


    public function horariosAdmin()
    {
        return $this->hasMany(
            HorarioPersonalAdmin::class,
            'cod_usu',
            'cod_usu'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ÁREAS INSTITUCIONALES
    |--------------------------------------------------------------------------
    */

    public static array $areasEstaticas = [
        'ARE_0001' =>
        'DIRECCIÓN GENERAL',

        'ARE_0002' =>
        'COORDINACIÓN DE PROGRAMAS Y SERVICIOS',

        'ARE_0003' =>
        'ÁREA ADMINISTRATIVA Y REGISTRO INSTITUCIONAL',

        'ARE_0004' =>
        'ÁREA DE ATENCIÓN MÉDICA',

        'ARE_0005' =>
        'ÁREA DE PSICOLOGÍA Y SEGUIMIENTO COGNITIVO',
    ];


    /*
    |--------------------------------------------------------------------------
    | PERSONAL DE SALUD
    |--------------------------------------------------------------------------
    */

    public function getPersonalSaludAttribute()
    {
        $rol =
            $this->rol_principal;


        if (
            in_array(
                $rol,
                [
                    'ENFERMEROS',
                    'MEDICO GENERAL/GERIATRA',
                    'PSICOLOGO/A',
                    'PEDAGOGO',
                    'NUTRICIONISTA',
                    'FISIOTERAPEUTA',
                ],
                true
            )
        ) {
            return (object) [
                'tipo_personal_salud' =>
                $this->categoria_institucional,

                'especialidad' =>
                (object) [
                    'nombre' =>
                    $this->categoria_institucional,

                    'nombre_especialidad' =>
                    $this->categoria_institucional,

                    'cod_esp' =>
                    $rol,
                ],

                'cod_esp' =>
                $rol,

                'fecha_ingreso' =>
                $this->created_at,

                'fecha_ing' =>
                $this->created_at,

                'institucion_formacion' =>
                $this->observaciones,

                'anios_exp' =>
                0,

                'matricula_prof' =>
                null,

                'subtipo_enfermeria' =>
                null,
            ];
        }


        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | PERSONAL ADMINISTRATIVO
    |--------------------------------------------------------------------------
    */

    public function getPersonalAdminAttribute()
    {
        $rol =
            $this->rol_principal;


        if (
            in_array(
                $rol,
                [
                    'SUPERADMINISTRADOR',
                    'ADMINISTRADOR',
                ],
                true
            )
        ) {
            return (object) [
                'cargoAdmin' =>
                (object) [
                    'nombre' =>
                    $this->categoria_institucional,

                    'cod_cargo_admin' =>
                    $rol,
                ],

                'cargo' =>
                (object) [
                    'nombre_cargo' =>
                    $this->categoria_institucional,
                ],

                'cod_cargo_admin' =>
                $rol,

                'fecha_ingreso' =>
                $this->created_at,

                'anios_exp' =>
                0,
            ];
        }


        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | ÁREA
    |--------------------------------------------------------------------------
    */

    public function getAreaInstitucionalAttribute()
    {
        return $this
            ->areaInstitucional()
            ->first();
    }


    public function areaInstitucional()
    {
        return $this->belongsTo(
            AreaInstitucional::class,
            'cod_area',
            'cod_area'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ROL PRINCIPAL
    |--------------------------------------------------------------------------
    */

    public function getRolPrincipalAttribute(): ?string
    {
        return $this
            ->roles
            ->first()
            ?->name;
    }


    /*
    |--------------------------------------------------------------------------
    | TIPO DE PERSONAL
    |--------------------------------------------------------------------------
    */

    public function getTipoPersonalAttribute(): string
    {
        if (
            in_array(
                $this->rol_principal,
                [
                    'SUPERADMINISTRADOR',
                    'ADMINISTRADOR',
                ],
                true
            )
        ) {
            return 'admin';
        }


        if (
            in_array(
                $this->rol_principal,
                [
                    'ENFERMEROS',
                    'MEDICO GENERAL/GERIATRA',
                    'PSICOLOGO/A',
                    'PEDAGOGO',
                    'NUTRICIONISTA',
                    'FISIOTERAPEUTA',
                ],
                true
            )
        ) {
            return 'salud';
        }


        return 'otro';
    }


    /*
    |--------------------------------------------------------------------------
    | CATEGORÍA INSTITUCIONAL
    |--------------------------------------------------------------------------
    */

    public function getCategoriaInstitucionalAttribute(): string
    {
        return match ($this->rol_principal) {
            'ENFERMEROS' =>
            'Enfermería',

            'MEDICO GENERAL/GERIATRA' =>
            'Médico/Geriatría',

            'PSICOLOGO/A' =>
            'Psicología',

            'NUTRICIONISTA' =>
            'Nutrición',

            'FISIOTERAPEUTA' =>
            'Fisioterapia',

            'PEDAGOGO' =>
            'Pedagogía',

            'SUPERADMINISTRADOR',
            'ADMINISTRADOR' =>
            'Administrativo',

            default =>
            $this->rol_principal
                ?? 'Sistema',
        };
    }


    /*
    |--------------------------------------------------------------------------
    | ÁREA VIRTUAL SEGÚN ROL
    |--------------------------------------------------------------------------
    */

    public function getCodAreaVirtualAttribute(): ?string
    {
        return match ($this->rol_principal) {
            'SUPERADMINISTRADOR',
            'ADMINISTRADOR' =>
            'ARE_0003',

            'PSICOLOGO/A',
            'PEDAGOGO' =>
            'ARE_0005',

            'ENFERMEROS',
            'MEDICO GENERAL/GERIATRA',
            'NUTRICIONISTA',
            'FISIOTERAPEUTA' =>
            'ARE_0004',

            default =>
            null,
        };
    }


    /**
     * Retorna el área almacenada o,
     * como fallback, el área calculada por rol.
     */
    public function getCodAreaAttribute(): ?string
    {
        return $this->attributes['cod_area']
            ?? $this->cod_area_virtual;
    }


    /**
     * Setter del código de área.
     */
    public function setCodAreaAttribute(
        $value
    ): void {
        $this->attributes['cod_area'] =
            $value ?: null;
    }


    /*
    |--------------------------------------------------------------------------
    | ASIGNACIONES DE TURNO
    |--------------------------------------------------------------------------
    */

    public function getAsignacionesTurnoAttribute()
    {
        $salud =
            $this->horariosSalud;

        $admin =
            $this->horariosAdmin;

        $mapped =
            collect();


        foreach (
            $salud as $horario
        ) {
            $mapped->push(
                (object) [
                    'estado' =>
                    $horario->estado === 'ACTIVO'
                        ? 'ACTIVA'
                        : 'INACTIVA',

                    'turno' =>
                    (object) [
                        'nombre' =>
                        $horario->turno,
                    ],
                ]
            );
        }


        foreach (
            $admin as $horario
        ) {
            $mapped->push(
                (object) [
                    'estado' =>
                    $horario->estado === 'ACTIVO'
                        ? 'ACTIVA'
                        : 'INACTIVA',

                    'turno' =>
                    (object) [
                        'nombre' =>
                        $horario->turno,
                    ],
                ]
            );
        }


        return $mapped;
    }
}
