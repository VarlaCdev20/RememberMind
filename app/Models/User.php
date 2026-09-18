<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, LogsActivity, Notifiable;

    protected $table = 'usuarios';
    protected $primaryKey = 'cod_usuario';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['cod_usuario', 'correo', 'contrasena', 'foto', 'estado'];
    protected $hidden = ['contrasena'];

    protected function casts(): array
    {
        return ['contrasena' => 'hashed'];
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

    public function personal(): HasOne
    {
        return $this->hasOne(Personal::class, 'cod_usuario', 'cod_usuario');
    }

    public function contactos(): HasMany
    {
        return $this->hasMany(Contacto::class, 'cod_usuario', 'cod_usuario');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['correo', 'foto', 'estado'])->logOnlyDirty();
    }
}
