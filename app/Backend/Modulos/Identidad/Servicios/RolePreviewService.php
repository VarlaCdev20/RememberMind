<?php

namespace App\Backend\Modulos\Identidad\Servicios;

use App\Models\User;
use Illuminate\Contracts\Session\Session;
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

    public function __construct(private readonly Session $session)
    {
    }

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

        $this->session->put(self::SESSION_KEY, $role);
    }

    public function clear(): void
    {
        $this->session->forget(self::SESSION_KEY);
    }

    public function activeRole(?User $user = null): ?string
    {
        $role = $this->session->get(self::SESSION_KEY);

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
