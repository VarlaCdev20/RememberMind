<?php

namespace App\Modulos\Identidad\Aplicacion\Acciones;

use App\Models\User;
use App\Modulos\Identidad\Aplicacion\Contratos\EstadoPrevisualizacion;
use Illuminate\Validation\ValidationException;

class RolePreviewService
{
    public const SESSION_KEY = 'preview_role';

    private const ROLES = [
        'SUPERADMINISTRADOR' => 'Superadministrador',
        'GERENTE' => 'Gerencia',
        'ADMINISTRADOR' => 'Administración',
        'MEDICO GENERAL/GERIATRA' => 'Médico/Geriatra',
        'ENFERMEROS' => 'Enfermería',
        'PSICOLOGO/A' => 'Psicología',
        'PEDAGOGO' => 'Pedagogía',
        'NUTRICIONISTA' => 'Nutrición',
        'FISIOTERAPEUTA' => 'Fisioterapia',
        'FAMILIAR' => 'Familiar',
    ];

    public function __construct(private readonly EstadoPrevisualizacion $estado) {}

    public function availableRoles(): array
    {
        return self::ROLES;
    }

    public function activate(User $user, string $role): void
    {
        abort_unless($user->hasRole('SUPERADMINISTRADOR'), 403);

        if (! array_key_exists($role, self::ROLES)) {
            throw ValidationException::withMessages([
                'role' => 'El rol seleccionado no está disponible para previsualización.',
            ]);
        }

        if ($role === 'SUPERADMINISTRADOR') {
            $this->clear();

            return;
        }

        $this->estado->guardar($role);
    }

    public function clear(): void
    {
        $this->estado->limpiar();
    }

    public function activeRole(?User $user = null): ?string
    {
        $role = $this->estado->obtener();

        if (! is_string($role)) {
            return null;
        }

        if (! $user?->hasRole('SUPERADMINISTRADOR') || ! array_key_exists($role, self::ROLES) || $role === 'SUPERADMINISTRADOR') {
            $this->clear();

            return null;
        }

        return $role;
    }

    public function isActive(?User $user = null): bool
    {
        return $this->activeRole($user) !== null;
    }

    public function label(?User $user = null): ?string
    {
        $role = $this->activeRole($user);

        return $role ? self::ROLES[$role] : null;
    }
}
