<?php

namespace App\Modulos\Alertas\Presentacion\Livewire\Paginas;

use App\Modulos\Alertas\Aplicacion\Consultas\CamposRegistroEspecialidad;
use App\Modulos\Alertas\Aplicacion\Consultas\ExperienciaAlertas;
use App\Modulos\Identidad\Aplicacion\Acciones\RolePreviewService;
use Livewire\Component;

class CompararAlertasRoles extends Component
{
    public string $rol = 'ADMINISTRADOR';

    public function seleccionarRol(string $rol): void
    {
        $this->autorizar();
        abort_unless(array_key_exists($rol, app(RolePreviewService::class)->availableRoles()), 422);
        $this->rol = $rol;
        $this->dispatch('alertas-rol-seleccionado');
    }

    private function autorizar(): void
    {
        abort_unless(auth()->user()?->estado === 'ACTIVO' && auth()->user()->can('alertas.ver')
            && auth()->user()->hasAnyRole(['SUPERADMINISTRADOR', 'ADMINISTRADOR']), 403);
    }

    public function render()
    {
        $this->autorizar();
        $roles = app(RolePreviewService::class)->availableRoles();
        abort_unless(array_key_exists($this->rol, $roles), 422);
        $perfil = app(ExperienciaAlertas::class)->paraRol($this->rol);
        $formulario = app(CamposRegistroEspecialidad::class)->paraRol($this->rol);
        $presentacionRoles = [
            'ENFERMEROS' => ['ph-heartbeat', 'Cuidado y continuidad del turno'],
            'MEDICO GENERAL/GERIATRA' => ['ph-stethoscope', 'Valoración y decisión médica'],
            'PSICOLOGO/A' => ['ph-brain', 'Bienestar y conducta'],
            'NUTRICIONISTA' => ['ph-bowl-food', 'Alimentación e hidratación'],
            'FISIOTERAPEUTA' => ['ph-person-simple-walk', 'Movilidad y autonomía'],
            'PEDAGOGO' => ['ph-book-open', 'Participación y aprendizaje'],
            'GERENTE' => ['ph-buildings', 'Supervisión institucional'],
            'ADMINISTRADOR' => ['ph-clipboard-text', 'Coordinación diaria'],
            'SUPERADMINISTRADOR' => ['ph-shield-check', 'Supervisión y trazabilidad'],
            'FAMILIAR' => ['ph-house-line', 'Información del residente vinculado'],
        ];

        return view('livewire.alertas.comparar-roles', compact('roles', 'perfil', 'formulario', 'presentacionRoles'))->layout('layouts.sistema');
    }
}
