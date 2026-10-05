<?php

namespace App\Frontend\Livewire\Medico\Clinica;

use App\Backend\Modulos\Clinica\Acciones\DefinirObjetivoSignoVitalAction;
use App\Backend\Modulos\Identidad\Servicios\RolePreviewService;
use App\Models\ObjetivoSignoVital;
use App\Models\Residente;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class ObjetivosSignosVitalesPanel extends Component
{
    public string $codResidente;
    public string $parametro = 'saturacion_oxigeno';
    public string $minObjetivo = '';
    public string $maxObjetivo = '';
    public string $minCritico = '';
    public string $maxCritico = '';
    public string $motivo = '';
    public string $codObjetivoRetiro = '';
    public string $motivoRetiro = '';
    public bool $mostrarHistorial = false;

    public function mount(string $residente): void
    {
        $usuario = auth()->user();
        abort_unless($usuario?->estado === 'ACTIVO'
            && $usuario->can('objetivos_signos_vitales.ver')
            && $usuario->hasAnyRole(['MEDICO GENERAL/GERIATRA', 'SUPERADMINISTRADOR']), 403);
        Gate::authorize('view', Residente::query()->findOrFail($residente));
        $this->codResidente = $residente;
    }

    public function guardar(): void
    {
        abort_unless($this->puedeGestionar(), 403);
        app(DefinirObjetivoSignoVitalAction::class)->ejecutar($this->codResidente, [
            'parametro' => $this->parametro,
            'min_objetivo' => $this->minObjetivo === '' ? null : $this->minObjetivo,
            'max_objetivo' => $this->maxObjetivo === '' ? null : $this->maxObjetivo,
            'min_critico' => $this->minCritico === '' ? null : $this->minCritico,
            'max_critico' => $this->maxCritico === '' ? null : $this->maxCritico,
            'motivo' => $this->motivo,
        ], auth()->user());
        $this->reset(['minObjetivo', 'maxObjetivo', 'minCritico', 'maxCritico', 'motivo']);
        $this->dispatch('rm-toast', ['icon' => 'success', 'title' => 'Objetivo médico registrado.']);
    }

    public function retirar(): void
    {
        abort_unless($this->puedeGestionar(), 403);
        abort_unless(ObjetivoSignoVital::query()->whereKey($this->codObjetivoRetiro)
            ->where('cod_residente', $this->codResidente)->exists(), 404);
        app(DefinirObjetivoSignoVitalAction::class)->retirar(
            $this->codObjetivoRetiro, $this->motivoRetiro, auth()->user());
        $this->reset(['codObjetivoRetiro', 'motivoRetiro']);
        $this->dispatch('rm-toast', ['icon' => 'success', 'title' => 'Objetivo médico retirado.']);
    }

    public function puedeGestionar(): bool
    {
        $usuario = auth()->user();
        return $usuario?->estado === 'ACTIVO'
            && $usuario->hasRole('MEDICO GENERAL/GERIATRA')
            && $usuario->can('objetivos_signos_vitales.gestionar')
            && ! app(RolePreviewService::class)->isActive($usuario);
    }

    public function render()
    {
        $residente = Residente::query()->findOrFail($this->codResidente);
        Gate::authorize('view', $residente);
        $objetivos = ObjetivoSignoVital::query()->where('cod_residente', $this->codResidente)
            ->where('estado', 'VIGENTE')->orderBy('parametro')->get();
        $historial = $this->mostrarHistorial
            ? ObjetivoSignoVital::query()->where('cod_residente', $this->codResidente)
                ->whereIn('estado', ['REEMPLAZADO', 'ANULADO'])->orderByDesc('vigente_desde')->get()
            : collect();

        return view('livewire.clinica.objetivos-signos-vitales-panel', [
            'residente' => $residente,
            'objetivos' => $objetivos,
            'historial' => $historial,
            'parametros' => ObjetivoSignoVital::PARAMETROS,
        ])->layout('layouts.sistema');
    }
}
